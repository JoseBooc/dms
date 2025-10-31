<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Creating sample staff users...\n";

$staffUsers = [
    [
        'name' => 'John Smith',
        'email' => 'john.staff@dms.local',
        'password' => Hash::make('password'),
        'role' => 'staff',
        'email_verified_at' => now(),
    ],
    [
        'name' => 'Sarah Johnson',
        'email' => 'sarah.staff@dms.local',
        'password' => Hash::make('password'),
        'role' => 'staff',
        'email_verified_at' => now(),
    ],
    [
        'name' => 'Mike Wilson',
        'email' => 'mike.staff@dms.local',
        'password' => Hash::make('password'),
        'role' => 'staff',
        'email_verified_at' => now(),
    ],
];

foreach ($staffUsers as $userData) {
    $user = User::updateOrCreate(
        ['email' => $userData['email']],
        $userData
    );
    
    echo "Created staff user: {$user->name} ({$user->email})\n";
}

echo "\nSample staff users created successfully!\n";
echo "You can now log in with any of these accounts using password: 'password'\n";
echo "\nStaff users created:\n";
foreach ($staffUsers as $staff) {
    echo "- {$staff['name']}: {$staff['email']}\n";
}