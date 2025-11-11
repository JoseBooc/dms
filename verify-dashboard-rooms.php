<?php

/**
 * Dashboard Room Calculation Verification
 * 
 * This script verifies the updated dashboard room counting logic
 * 
 * Run: php verify-dashboard-rooms.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Room;

echo "\n=== DASHBOARD ROOM CALCULATION VERIFICATION ===\n\n";

// Get all rooms with their occupancy details
$rooms = Room::select('room_number', 'type', 'capacity', 'current_occupants', 'status')
    ->orderBy('room_number')
    ->get();

echo "Total Rooms: " . $rooms->count() . "\n\n";

echo "=== ROOM DETAILS ===\n";
foreach ($rooms as $room) {
    $occupancyDisplay = "{$room->current_occupants}/{$room->capacity}";
    $hasSpace = $room->current_occupants < $room->capacity;
    $isFull = $room->current_occupants >= $room->capacity;
    
    echo "Room {$room->room_number} ({$room->type}):\n";
    echo "  Occupancy: {$occupancyDisplay}\n";
    echo "  Status: {$room->status}\n";
    echo "  Classification: " . ($hasSpace ? "✅ AVAILABLE (has space)" : "🔴 OCCUPIED (full)") . "\n";
    echo "\n";
}

echo "=== NEW CALCULATION LOGIC ===\n";
$availableRooms = Room::whereColumn('current_occupants', '<', 'capacity')->count();
$occupiedRooms = Room::where('current_occupants', '>', 0)->count();

echo "Available Rooms (current_occupants < capacity): {$availableRooms}\n";
echo "Occupied Rooms (current_occupants > 0): {$occupiedRooms}\n";
echo "Note: Rooms can be counted in BOTH categories if partially occupied\n";

echo "\n=== BREAKDOWN ===\n";
echo "Rooms with space available:\n";
$availableList = Room::whereColumn('current_occupants', '<', 'capacity')->get();
foreach ($availableList as $room) {
    $remaining = $room->capacity - $room->current_occupants;
    $bothCategories = $room->current_occupants > 0 ? " [Also counted in Occupied]" : "";
    echo "  - Room {$room->room_number}: {$room->current_occupants}/{$room->capacity} (space for {$remaining} more){$bothCategories}\n";
}

echo "\nRooms with occupants:\n";
$occupiedList = Room::where('current_occupants', '>', 0)->get();
if ($occupiedList->count() > 0) {
    foreach ($occupiedList as $room) {
        $bothCategories = $room->current_occupants < $room->capacity ? " [Also counted in Available]" : "";
        echo "  - Room {$room->room_number}: {$room->current_occupants}/{$room->capacity}{$bothCategories}\n";
    }
} else {
    echo "  No rooms with occupants\n";
}

echo "\n=== LOGIC EXPLANATION ===\n";
echo "OLD Logic:\n";
echo "  - Available: status='available' AND has no assignments\n";
echo "  - Occupied: has any assignments\n";
echo "  ❌ Problem: Mutually exclusive - couldn't show partial occupancy properly\n";

echo "\nNEW Logic (DUAL COUNTING):\n";
echo "  - Available: current_occupants < capacity (has space)\n";
echo "  - Occupied: current_occupants > 0 (has tenants)\n";
echo "  ✅ Solution: Room with 1/2 occupancy shows in BOTH categories\n";
echo "  ✅ Available = 'Can accept more tenants'\n";
echo "  ✅ Occupied = 'Currently housing tenants'\n";

echo "\n=== VERIFICATION COMPLETE ===\n\n";
