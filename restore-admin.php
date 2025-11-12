<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'name' => 'Admin',
    'email' => 'admin@areja.com',
    'password' => Hash::make('password'),
    'role' => 'admin',
    'status' => 'active',
    'gender' => 'male',
    'email_verified_at' => now(),
]);

echo "==========================================\n";
echo "     ADMIN USER RESTORED SUCCESSFULLY     \n";
echo "==========================================\n\n";
echo "Email: " . $admin->email . "\n";
echo "Password: password\n";
echo "Role: " . $admin->role . "\n";
echo "Status: " . $admin->status . "\n";
echo "ID: " . $admin->id . "\n\n";
echo "You can now log in to the admin panel.\n";
echo "==========================================\n";
