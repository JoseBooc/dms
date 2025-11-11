<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\RoomAssignment;
use App\Models\Tenant;
use App\Models\User;

echo "\n========================================\n";
echo "RELATIONSHIP DEBUG\n";
echo "========================================\n\n";

echo "RoomAssignments with tenant_id details:\n";
echo "--------------------------------------------\n";

$assignments = RoomAssignment::with('room')->get();

foreach ($assignments as $assignment) {
    echo "Assignment ID: {$assignment->id}\n";
    echo "tenant_id value: {$assignment->tenant_id}\n";
    
    // Try to find in Tenant model
    $tenant = Tenant::find($assignment->tenant_id);
    if ($tenant) {
        echo "Found in Tenant model: {$tenant->first_name} {$tenant->last_name}\n";
        if ($tenant->user_id) {
            $user = User::find($tenant->user_id);
            echo "Linked User: " . ($user ? $user->name : 'Not found') . "\n";
        }
    }
    
    // Try to find in User model directly
    $userDirect = User::find($assignment->tenant_id);
    if ($userDirect) {
        echo "Found in User model (direct): {$userDirect->name}\n";
    }
    
    echo "Room: " . ($assignment->room ? $assignment->room->room_number : 'N/A') . "\n";
    echo "Status: {$assignment->status}\n";
    echo "---\n";
}

echo "\n========================================\n";
echo "All Tenants in Tenant table:\n";
echo "========================================\n\n";

$tenants = Tenant::with('user')->get();
foreach ($tenants as $tenant) {
    echo "Tenant ID: {$tenant->id}\n";
    echo "Name: {$tenant->first_name} {$tenant->last_name}\n";
    echo "User ID: " . ($tenant->user_id ?? 'NULL') . "\n";
    if ($tenant->user) {
        echo "Linked User: {$tenant->user->name}\n";
    }
    echo "---\n";
}

echo "\n========================================\n";
echo "All Users with role=tenant:\n";
echo "========================================\n\n";

$users = User::where('role', 'tenant')->get();
foreach ($users as $user) {
    echo "User ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    
    // Check if they have a Tenant profile
    $tenantProfile = Tenant::where('user_id', $user->id)->first();
    if ($tenantProfile) {
        echo "Has Tenant profile (ID: {$tenantProfile->id})\n";
    } else {
        echo "No Tenant profile\n";
    }
    echo "---\n";
}
