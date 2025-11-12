<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "     ORPHANED RECORDS CHECK               \n";
echo "==========================================\n\n";

// Check for bills with missing tenants
$billsWithoutTenants = DB::table('bills')
    ->leftJoin('users', 'bills.tenant_id', '=', 'users.id')
    ->whereNull('users.id')
    ->count();
    
echo "Bills without tenants: {$billsWithoutTenants}\n";

// Check for bills with missing rooms
$billsWithoutRooms = DB::table('bills')
    ->leftJoin('rooms', 'bills.room_id', '=', 'rooms.id')
    ->whereNull('rooms.id')
    ->whereNotNull('bills.room_id')
    ->count();
    
echo "Bills without rooms: {$billsWithoutRooms}\n";

// Check for utility readings with missing types
$readingsWithoutTypes = DB::table('utility_readings')
    ->leftJoin('utility_types', 'utility_readings.utility_type_id', '=', 'utility_types.id')
    ->whereNull('utility_types.id')
    ->count();
    
echo "Utility readings without types: {$readingsWithoutTypes}\n";

// Check for utility readings with missing recorded_by users
$readingsWithoutRecorder = DB::table('utility_readings')
    ->leftJoin('users', 'utility_readings.recorded_by', '=', 'users.id')
    ->whereNull('users.id')
    ->whereNotNull('utility_readings.recorded_by')
    ->count();
    
echo "Utility readings without recorder: {$readingsWithoutRecorder}\n";

// Check for maintenance requests with missing assignees
$requestsWithoutAssignee = DB::table('maintenance_requests')
    ->leftJoin('users', 'maintenance_requests.assigned_to', '=', 'users.id')
    ->whereNull('users.id')
    ->whereNotNull('maintenance_requests.assigned_to')
    ->count();
    
echo "Maintenance requests without assignee: {$requestsWithoutAssignee}\n";

// Check for room assignments with missing tenants
$assignmentsWithoutTenants = DB::table('room_assignments')
    ->leftJoin('users', 'room_assignments.tenant_id', '=', 'users.id')
    ->whereNull('users.id')
    ->count();
    
echo "Room assignments without tenants: {$assignmentsWithoutTenants}\n";

echo "\n==========================================\n";

if ($billsWithoutTenants > 0 || $billsWithoutRooms > 0 || $readingsWithoutTypes > 0 || 
    $readingsWithoutRecorder > 0 || $requestsWithoutAssignee > 0 || $assignmentsWithoutTenants > 0) {
    echo "⚠ WARNING: Orphaned records found!\n";
    echo "These may cause 'Attempt to read property on null' errors.\n";
} else {
    echo "✓ No orphaned records found.\n";
}

echo "==========================================\n";
