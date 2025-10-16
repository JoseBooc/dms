<?php

require_once 'vendor/autoload.php';

use App\Models\Complaint;
use App\Models\Tenant;
use App\Models\Room;

// Get a tenant for testing
$tenant = Tenant::first();
$room = Room::first();

if ($tenant && $room) {
    // Create sample complaints
    $complaints = [
        [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'title' => 'Noisy neighbors during late hours',
            'description' => 'My neighbors in the adjacent room are consistently loud during late hours (after 11 PM), playing music and talking loudly. This is affecting my sleep and study schedule.',
            'category' => 'noise',
            'priority' => 'medium',
            'status' => 'open',
        ],
        [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'title' => 'Shared bathroom cleanliness issues',
            'description' => 'The shared bathroom on our floor is not being maintained properly. There are hygiene issues and it needs more frequent cleaning.',
            'category' => 'cleanliness',
            'priority' => 'high',
            'status' => 'open',
        ],
        [
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'title' => 'WiFi connectivity problems in room',
            'description' => 'Internet connection in my room is very slow and frequently disconnects. This is affecting my online classes and work.',
            'category' => 'facilities',
            'priority' => 'high',
            'status' => 'investigating',
        ],
    ];

    foreach ($complaints as $complaint) {
        Complaint::create($complaint);
    }

    echo "Sample complaints created successfully!\n";
    echo "Tenant: {$tenant->first_name} {$tenant->last_name}\n";
    echo "Room: {$room->room_number}\n";
    echo "Total complaints created: " . count($complaints) . "\n";
} else {
    echo "No tenant or room found for creating sample complaints.\n";
}