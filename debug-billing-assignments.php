<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\RoomAssignment;

echo "\n========================================\n";
echo "ROOM ASSIGNMENT DEBUG - BILLING SYSTEM\n";
echo "========================================\n\n";

echo "Checking ALL room assignments:\n";
echo "--------------------------------------------\n";

$assignments = RoomAssignment::with('tenant', 'room')->get();

foreach ($assignments as $assignment) {
    echo "Tenant: " . $assignment->tenant->name . "\n";
    echo "Room: " . ($assignment->room ? $assignment->room->room_number : 'N/A') . "\n";
    echo "Status: " . $assignment->status . "\n";
    echo "Start Date: " . $assignment->start_date . "\n";
    if ($assignment->end_date) {
        echo "End Date: " . $assignment->end_date . "\n";
    }
    echo "---\n";
}

echo "\n========================================\n";
echo "ACTIVE assignments ONLY:\n";
echo "========================================\n\n";

$activeAssignments = RoomAssignment::where('status', 'active')
    ->with('tenant', 'room')
    ->get();

foreach ($activeAssignments as $assignment) {
    echo "✅ " . $assignment->tenant->name . " → " . ($assignment->room ? $assignment->room->room_number : 'N/A') . "\n";
}

echo "\n========================================\n";
echo "Tenant-Room mapping (BILLING VIEW):\n";
echo "========================================\n\n";

$tenants = User::where('role', 'tenant')
    ->with(['roomAssignments' => function ($query) {
        $query->where('status', 'active')->with('room');
    }])
    ->get();

foreach ($tenants as $tenant) {
    $activeAssignment = $tenant->roomAssignments->first();
    $roomInfo = $activeAssignment && $activeAssignment->room 
        ? $activeAssignment->room->room_number 
        : 'Unassigned';
    
    echo "Tenant: {$tenant->name}\n";
    echo "Shown as: {$tenant->name} ({$roomInfo})\n";
    
    if ($activeAssignment) {
        echo "Assignment ID: {$activeAssignment->id}\n";
        echo "Room ID: {$activeAssignment->room_id}\n";
        echo "Status: {$activeAssignment->status}\n";
    }
    echo "---\n";
}

echo "\n========================================\n";
echo "USER-TENANT relationship check:\n";
echo "========================================\n\n";

$users = User::where('role', 'tenant')->get();
foreach ($users as $user) {
    echo "User ID: {$user->id} | Name: {$user->name}\n";
    
    $assignments = RoomAssignment::where('tenant_id', $user->id)
        ->where('status', 'active')
        ->with('room')
        ->get();
    
    foreach ($assignments as $assign) {
        echo "  → Assignment ID: {$assign->id}\n";
        echo "  → tenant_id column: {$assign->tenant_id}\n";
        echo "  → Room: " . ($assign->room ? $assign->room->room_number : 'N/A') . "\n";
    }
    
    if ($assignments->isEmpty()) {
        echo "  → No active assignments\n";
    }
    echo "\n";
}
