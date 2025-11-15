<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\MaintenanceRequest;
use App\Models\Room;

// Boot Laravel application
$app = new Application(realpath(__DIR__));
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing notification triggers...\n";

// Find a tenant user and a room
$tenantUser = User::where('role', 'tenant')->first();
$room = Room::first();

if (!$tenantUser) {
    echo "No tenant users found. Cannot test maintenance request creation.\n";
    exit;
}

if (!$room) {
    echo "No rooms found. Cannot test maintenance request creation.\n";
    exit;
}

echo "Found tenant: {$tenantUser->first_name} {$tenantUser->last_name}\n";
echo "Using room: {$room->room_number}\n";

// Count notifications before
$notificationsBefore = DB::table('notifications')->count();
echo "Notifications before test: {$notificationsBefore}\n";

// Test 1: Create a maintenance request (should trigger MaintenanceRequestCreatedNotification)
try {
    echo "Creating maintenance request...\n";
    
    $maintenanceRequest = MaintenanceRequest::create([
        'tenant_id' => $tenantUser->tenant->id ?? $tenantUser->id, // Handle both tenant table and direct user
        'room_id' => $room->id,
        'description' => 'Test notification system - broken faucet',
        'area' => 'bathroom',
        'priority' => 'medium',
        'status' => 'pending'
    ]);
    
    echo "✓ Maintenance request created with ID: {$maintenanceRequest->id}\n";
    
} catch (Exception $e) {
    echo "✗ Failed to create maintenance request: " . $e->getMessage() . "\n";
}

// Count notifications after
$notificationsAfter = DB::table('notifications')->count();
echo "Notifications after test: {$notificationsAfter}\n";
echo "New notifications created: " . ($notificationsAfter - $notificationsBefore) . "\n";

// Show the latest notifications
$latestNotifications = DB::table('notifications')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

echo "\nLatest notifications:\n";
foreach ($latestNotifications as $notification) {
    $data = json_decode($notification->data, true);
    echo "- Type: {$data['type']}, Title: {$data['title']}\n";
    echo "  Message: {$data['message']}\n";
    echo "  Created: {$notification->created_at}\n\n";
}

echo "Notification trigger test completed.\n";