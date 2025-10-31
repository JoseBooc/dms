<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MaintenanceRequest;
use App\Models\Complaint;

echo "Assigning maintenance requests and complaints to staff...\n";

// Get staff users
$staffUsers = User::where('role', 'staff')->get();

if ($staffUsers->count() === 0) {
    echo "No staff users found. Please run create-staff-users.php first.\n";
    exit(1);
}

// Assign maintenance requests to staff
$maintenanceRequests = MaintenanceRequest::whereNull('assigned_to')->limit(10)->get();
foreach ($maintenanceRequests as $index => $request) {
    $staffUser = $staffUsers->get($index % $staffUsers->count());
    $request->update(['assigned_to' => $staffUser->id]);
    echo "Assigned maintenance request #{$request->id} to {$staffUser->name}\n";
}

// Assign complaints to staff
$complaints = Complaint::whereNull('assigned_to')->limit(10)->get();
foreach ($complaints as $index => $complaint) {
    $staffUser = $staffUsers->get($index % $staffUsers->count());
    $complaint->update(['assigned_to' => $staffUser->id]);
    echo "Assigned complaint #{$complaint->id} to {$staffUser->name}\n";
}

echo "\nAssignment completed!\n";
echo "Staff users now have maintenance requests and complaints assigned to them.\n";
echo "\nYou can test the staff views by logging in with any staff account:\n";
foreach ($staffUsers as $staff) {
    echo "- {$staff->name}: {$staff->email} (password: 'password')\n";
}