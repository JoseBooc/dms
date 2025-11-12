<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "     DELETING ALL ROOMS FROM SYSTEM       \n";
echo "==========================================\n\n";

// Get current rooms
$rooms = DB::table('rooms')->get(['id', 'room_number', 'type']);

echo "Current Rooms in System:\n";
echo "------------------------\n";
foreach ($rooms as $room) {
    echo "ID: {$room->id} - {$room->room_number} ({$room->type})\n";
}
echo "\nTotal Rooms: " . $rooms->count() . "\n\n";

if ($rooms->count() === 0) {
    echo "No rooms to delete.\n";
    exit(0);
}

// Check for related records
echo "Checking Related Records:\n";
echo "-------------------------\n";
$assignments = DB::table('room_assignments')->count();
$utilityReadings = DB::table('utility_readings')->count();
$bills = DB::table('bills')->count();

echo "Room Assignments: {$assignments}\n";
echo "Utility Readings: {$utilityReadings}\n";
echo "Bills: {$bills}\n\n";

// Perform deletion with foreign key checks disabled
echo "Performing Deletion...\n";
echo "----------------------\n";

try {
    DB::beginTransaction();
    
    // Disable foreign key checks temporarily
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    // Delete all rooms
    $deletedCount = DB::table('rooms')->delete();
    
    // Re-enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    DB::commit();
    
    echo "✓ Successfully deleted {$deletedCount} rooms\n\n";
    
    // Verify deletion
    $remainingRooms = DB::table('rooms')->count();
    
    echo "==========================================\n";
    echo "     DELETION COMPLETE                    \n";
    echo "==========================================\n\n";
    echo "Remaining Rooms: {$remainingRooms}\n";
    
    if ($remainingRooms === 0) {
        echo "✓ All rooms successfully removed from system\n";
    } else {
        echo "⚠ Warning: {$remainingRooms} rooms still remain\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "==========================================\n";
