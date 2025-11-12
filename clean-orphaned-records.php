<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "     CLEANING ORPHANED RECORDS            \n";
echo "==========================================\n\n";

// Clean utility readings without types
$deletedReadings = DB::table('utility_readings')
    ->leftJoin('utility_types', 'utility_readings.utility_type_id', '=', 'utility_types.id')
    ->whereNull('utility_types.id')
    ->delete();
    
echo "✓ Deleted {$deletedReadings} utility readings without types\n";

// Clean room assignments without tenants
$deletedAssignments = DB::table('room_assignments')
    ->leftJoin('users', 'room_assignments.tenant_id', '=', 'users.id')
    ->whereNull('users.id')
    ->delete();
    
echo "✓ Deleted {$deletedAssignments} room assignments without tenants\n";

echo "\n==========================================\n";
echo "     VERIFICATION                         \n";
echo "==========================================\n\n";

// Verify cleanup
$remainingOrphanedReadings = DB::table('utility_readings')
    ->leftJoin('utility_types', 'utility_readings.utility_type_id', '=', 'utility_types.id')
    ->whereNull('utility_types.id')
    ->count();
    
$remainingOrphanedAssignments = DB::table('room_assignments')
    ->leftJoin('users', 'room_assignments.tenant_id', '=', 'users.id')
    ->whereNull('users.id')
    ->count();

echo "Remaining utility readings without types: {$remainingOrphanedReadings}\n";
echo "Remaining room assignments without tenants: {$remainingOrphanedAssignments}\n\n";

if ($remainingOrphanedReadings === 0 && $remainingOrphanedAssignments === 0) {
    echo "✓ All orphaned records cleaned successfully!\n";
} else {
    echo "⚠ Some orphaned records remain.\n";
}

echo "==========================================\n";
