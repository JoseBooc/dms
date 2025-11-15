<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\User;
use App\Models\MaintenanceRequest;
use App\Models\Complaint;
use App\Models\UtilityReading;
use App\Notifications\MaintenanceRequestCreatedNotification;

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

echo "Testing notification system...\n";

// Test 1: Check if admin users exist
$adminUsers = User::where('role', 'admin')->get();
echo "Found " . $adminUsers->count() . " admin users.\n";

// Test 2: Check if any notifications exist
$notificationCount = DB::table('notifications')->count();
echo "Current notifications in database: " . $notificationCount . "\n";

// Test 3: Check if notification classes exist
$notificationClasses = [
    'MaintenanceRequestCreatedNotification',
    'ComplaintCreatedNotification', 
    'MaintenanceWorkStartedNotification',
    'ComplaintInvestigationStartedNotification',
    'ComplaintNotesUpdatedNotification',
    'MaintenanceWorkCompletedNotification',
    'ComplaintResolvedNotification',
    'NewUtilityReadingNotification',
    'BillOverdueNotification',
    'PenaltyChargeNotification',
    'StaffAssignmentNotification'
];

foreach ($notificationClasses as $className) {
    $fullClassName = "App\\Notifications\\{$className}";
    if (class_exists($fullClassName)) {
        echo "✓ {$className} class exists\n";
    } else {
        echo "✗ {$className} class missing\n";
    }
}

// Test 4: Check if observer classes exist
$observerClasses = [
    'BillObserver',
    'MaintenanceRequestObserver', 
    'ComplaintObserver',
    'UtilityReadingObserver'
];

foreach ($observerClasses as $className) {
    $fullClassName = "App\\Observers\\{$className}";
    if (class_exists($fullClassName)) {
        echo "✓ {$className} observer exists\n";
    } else {
        echo "✗ {$className} observer missing\n";
    }
}

echo "Notification system test completed.\n";