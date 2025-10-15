<?php

use App\Models\MaintenanceRequest;
use App\Models\RoomAssignment;

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Creating sample maintenance request...\n";

// Get the first active room assignment
$assignment = RoomAssignment::where('status', 'active')->first();

if (!$assignment) {
    echo "No active room assignments found!\n";
    exit;
}

echo "Found assignment for room {$assignment->room_id}, tenant {$assignment->tenant_id}\n";

// Create a sample maintenance request
$request = MaintenanceRequest::create([
    'tenant_id' => $assignment->tenant_id,
    'room_id' => $assignment->room_id,
    'description' => 'The bathroom faucet is leaking and needs repair. Water is dripping constantly.',
    'area' => 'Bathroom',
    'status' => 'pending',
    'priority' => 'medium',
    'photos' => null,
    'assigned_to' => null,
]);

echo "Created maintenance request ID: {$request->id}\n";
echo "Description: {$request->description}\n";
echo "Area: {$request->area}\n";
echo "Priority: {$request->priority}\n";
echo "Status: {$request->status}\n";

echo "Done! Tenant can now view this request in their maintenance requests page.\n";