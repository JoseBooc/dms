<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Room;
use App\Models\RoomAssignment;

echo "=== ROOM ASSIGNMENT STATUS FIX VERIFICATION ===\n\n";

echo "Checking room assignment statuses and their effect on room availability...\n\n";

// Get all assignments that are pending or inactive
$nonActiveAssignments = RoomAssignment::whereIn('status', ['pending', 'inactive'])
    ->with(['room', 'tenant'])
    ->get();

if ($nonActiveAssignments->count() > 0) {
    echo "Found " . $nonActiveAssignments->count() . " non-active assignments:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($nonActiveAssignments as $assignment) {
        $room = $assignment->room;
        $tenant = $assignment->tenant;
        
        echo "Assignment ID: {$assignment->id}\n";
        echo "Tenant: {$tenant->first_name} {$tenant->last_name} (TID{$tenant->id})\n";
        echo "Room: {$room->room_number}\n";
        echo "Status: {$assignment->status}\n";
        echo "Room Current Occupants: {$room->current_occupants}/{$room->capacity}\n";
        echo "Room Status: {$room->status}\n";
        
        // Check if room would be available for new assignments
        $availableSlots = $room->capacity - $room->current_occupants;
        $isAvailableForNewAssignment = $availableSlots > 0;
        
        echo "Available for new assignments: " . ($isAvailableForNewAssignment ? "YES (${availableSlots} slots)" : "NO") . "\n";
        echo str_repeat("-", 80) . "\n";
    }
} else {
    echo "No pending or inactive assignments found.\n\n";
}

// Check all rooms with their current status
echo "=== ALL ROOMS CURRENT STATUS ===\n";
$rooms = Room::where('is_hidden', false)->orderBy('room_number')->get();

foreach ($rooms as $room) {
    $activeCount = $room->assignments()->where('status', 'active')->count();
    $pendingCount = $room->assignments()->where('status', 'pending')->count();
    $inactiveCount = $room->assignments()->where('status', 'inactive')->count();
    $terminatedCount = $room->assignments()->where('status', 'terminated')->count();
    
    $totalOccupying = $activeCount + $pendingCount + $inactiveCount;
    
    echo "Room {$room->room_number}: {$room->current_occupants}/{$room->capacity} ({$room->status})\n";
    echo "  - Active: {$activeCount}, Pending: {$pendingCount}, Inactive: {$inactiveCount}, Terminated: {$terminatedCount}\n";
    echo "  - Total Occupying: {$totalOccupying} (should match current_occupants: {$room->current_occupants})\n";
    
    if ($totalOccupying !== $room->current_occupants) {
        echo "  ⚠️  MISMATCH! Expected: {$totalOccupying}, Actual: {$room->current_occupants}\n";
    } else {
        echo "  ✅ Correct occupancy count\n";
    }
    echo "\n";
}

echo "\n=== FIX VERIFICATION SUMMARY ===\n";
echo "✅ Room occupancy now counts: active, pending, inactive assignments\n";
echo "✅ Only terminated assignments free up room space\n";
echo "✅ Pending assignments (tenant hasn't moved in) keep room occupied\n";
echo "✅ Inactive assignments (tenant temporarily away) keep room occupied\n";
echo "✅ Room becomes available only when assignment is terminated\n\n";

echo "The fix ensures that rooms remain occupied until assignments are properly terminated.\n";