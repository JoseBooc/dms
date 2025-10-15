<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Create an admin user for testing
$admin = App\Models\User::updateOrCreate(
    ['email' => 'admin@dms.test'],
    [
        'name' => 'System Administrator',
        'email' => 'admin@dms.test',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]
);

echo "Admin user created successfully!\n";
echo "Email: admin@dms.test\n";
echo "Password: admin123\n";
echo "Role: admin\n";
echo "\nYou can now login as admin to see all maintenance requests in 'Maintenance Requests (Admin)'\n";