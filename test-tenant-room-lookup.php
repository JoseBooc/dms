<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING TENANT-ROOM ASSIGNMENT LOOKUP ===\n\n";

// Get all users with role 'tenant'
$tenantUsers = \App\Models\User::where('role', 'tenant')->with('tenant')->get();

foreach ($tenantUsers as $user) {
    echo "--- USER: {$user->first_name} {$user->last_name} (ID: {$user->id}) ---\n";
    
    if ($user->tenant) {
        echo "Tenant Record ID: {$user->tenant->id}\n";
        
        // Get room assignments for this tenant
        $assignments = \App\Models\RoomAssignment::where('tenant_id', $user->tenant->id)
            ->with('room')
            ->get();
        
        echo "Room Assignments: " . $assignments->count() . "\n";
        
        foreach ($assignments as $assignment) {
            echo "  - Assignment ID: {$assignment->id}\n";
            echo "    Room: {$assignment->room->room_number}\n";
            echo "    Status: {$assignment->status}\n";
            echo "    Start Date: " . ($assignment->start_date ? $assignment->start_date->format('M d, Y') : 'N/A') . "\n";
            
            if ($assignment->status === 'active') {
                echo "    ★ ACTIVE ASSIGNMENT\n";
            }
            echo "\n";
        }
    } else {
        echo "No tenant record found for this user!\n";
    }
    
    echo "\n";
}

echo "=== TEST COMPLETE ===\n";