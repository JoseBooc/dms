<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\Tenant;

echo "=== TESTING ROOM ASSIGNMENT STATUS FIX ===\n\n";

// Find room S004 which has the inactive assignment
$roomS004 = Room::where('room_number', 'S004')->first();

if (!$roomS004) {
    echo "Room S004 not found!\n";
    exit;
}

echo "Room S004 Current Status:\n";
echo "- Current Occupants: {$roomS004->current_occupants}/{$roomS004->capacity}\n";
echo "- Status: {$roomS004->status}\n";
echo "- Available slots: " . ($roomS004->capacity - $roomS004->current_occupants) . "\n\n";

// Check if room would be available for new assignments in Filament resource query
$availableRooms = Room::query()
    ->whereRaw('current_occupants < capacity')
    ->where('status', '!=', 'unavailable')
    ->where('is_hidden', false)
    ->where('room_number', 'S004')
    ->get();

echo "Would Room S004 appear in Filament room selection? " . 
     ($availableRooms->count() > 0 ? "YES ❌ (This is wrong!)" : "NO ✅ (This is correct!)") . "\n\n";

// Find a tenant without any assignments to test
$availableTenant = Tenant::whereDoesntHave('roomAssignments', function($query) {
    $query->whereIn('status', ['active', 'pending', 'inactive']);
})->first();

if ($availableTenant) {
    echo "Found available tenant for testing: {$availableTenant->first_name} {$availableTenant->last_name} (TID{$availableTenant->id})\n";
    
    // Test the validation logic that would run during assignment creation
    $currentAssignments = RoomAssignment::where('room_id', $roomS004->id)
        ->whereIn('status', ['active', 'pending', 'inactive'])
        ->count();
    
    echo "Current assignments in S004 (active/pending/inactive): {$currentAssignments}\n";
    echo "Room capacity: {$roomS004->capacity}\n";
    echo "Would assignment be blocked? " . 
         ($currentAssignments >= $roomS004->capacity ? "YES ✅ (Correct!)" : "NO ❌ (Wrong!)") . "\n\n";
} else {
    echo "No available tenants found for testing (all have assignments)\n\n";
}

echo "=== TEST SUMMARY ===\n";
echo "✅ Inactive assignment in S004 should keep room occupied: " . 
     ($roomS004->current_occupants >= $roomS004->capacity ? "PASS" : "FAIL") . "\n";
echo "✅ Room should not appear in available room list: " . 
     ($availableRooms->count() == 0 ? "PASS" : "FAIL") . "\n";
echo "✅ New assignments should be blocked: " . 
     (isset($currentAssignments) && $currentAssignments >= $roomS004->capacity ? "PASS" : "FAIL") . "\n\n";

echo "The fix prevents rooms with pending/inactive assignments from being assigned to other tenants.\n";