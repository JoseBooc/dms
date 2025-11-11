<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "\n========================================\n";
echo "TENANT DROPDOWN TEST\n";
echo "========================================\n\n";

echo "All tenants that will appear in dropdown:\n";
echo "--------------------------------------------\n";

$tenants = User::where('role', 'tenant')
    ->with(['roomAssignments' => function ($query) {
        $query->where('status', 'active')->with('room');
    }])
    ->get();

$count = 0;
foreach ($tenants as $tenant) {
    $count++;
    $activeAssignment = $tenant->roomAssignments->first();
    $roomInfo = $activeAssignment && $activeAssignment->room 
        ? ' (' . $activeAssignment->room->room_number . ')' 
        : ' (Unassigned)';
    
    echo "{$count}. {$tenant->name}{$roomInfo}\n";
    
    if ($activeAssignment && $activeAssignment->room) {
        echo "   ✅ Has active room assignment\n";
        echo "   Room ID: {$activeAssignment->room_id}\n";
        echo "   Room Rate: ₱" . number_format($activeAssignment->room->price ?? 0, 2) . "\n";
    } else {
        echo "   ⚠️  No active room assignment\n";
        echo "   Room fields will be cleared\n";
    }
    echo "\n";
}

echo "========================================\n";
echo "Total tenants in dropdown: {$count}\n";
echo "========================================\n\n";
