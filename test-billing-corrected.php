<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "\n========================================\n";
echo "BILLING FORM - CORRECTED MAPPING TEST\n";
echo "========================================\n\n";

echo "Tenants in dropdown (CORRECTED QUERY):\n";
echo "--------------------------------------------\n";

$users = User::where('role', 'tenant')
    ->with(['tenant.assignments' => function ($query) {
        $query->where('status', 'active')->with('room');
    }])
    ->get();

$count = 0;
foreach ($users as $user) {
    $count++;
    
    // Get tenant profile and active assignment (CORRECT PATH)
    $tenantProfile = $user->tenant;
    $activeAssignment = $tenantProfile ? $tenantProfile->assignments->first() : null;
    
    $roomInfo = $activeAssignment && $activeAssignment->room 
        ? ' (' . $activeAssignment->room->room_number . ')' 
        : ' (Unassigned)';
    
    echo "{$count}. {$user->name}{$roomInfo}\n";
    echo "   User ID: {$user->id}\n";
    
    if ($tenantProfile) {
        echo "   Tenant Profile ID: {$tenantProfile->id}\n";
        
        if ($activeAssignment && $activeAssignment->room) {
            echo "   ✅ Has active room assignment\n";
            echo "   Assignment ID: {$activeAssignment->id}\n";
            echo "   Room ID: {$activeAssignment->room_id}\n";
            echo "   Room Number: {$activeAssignment->room->room_number}\n";
            echo "   Room Rate: ₱" . number_format($activeAssignment->room->price ?? 0, 2) . "\n";
        } else {
            echo "   ⚠️  No active room assignment\n";
        }
    } else {
        echo "   ⚠️  No tenant profile\n";
    }
    
    echo "\n";
}

echo "========================================\n";
echo "Total users in dropdown: {$count}\n";
echo "========================================\n\n";

echo "VERIFICATION:\n";
echo "--------------------------------------------\n";
echo "Expected: Nicolas dela Pena should show (D102)\n";
echo "Expected: Jernelle Test should show (Unassigned)\n";
echo "========================================\n\n";
