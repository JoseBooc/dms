<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "==========================================\n";
echo "     ADMIN USER VERIFICATION              \n";
echo "==========================================\n\n";

$admin = User::where('email', 'admin@areja.com')->first();

if ($admin) {
    echo "✓ Admin user found\n\n";
    echo "ID: " . $admin->id . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Name: " . ($admin->name ?? 'NULL') . "\n";
    echo "First Name: " . ($admin->first_name ?? 'NULL') . "\n";
    echo "Last Name: " . ($admin->last_name ?? 'NULL') . "\n";
    echo "Role: " . $admin->role . "\n";
    echo "Status: " . $admin->status . "\n\n";
    
    if (!$admin->name || $admin->name === 'NULL') {
        echo "⚠ WARNING: Name field is missing!\n";
        echo "Updating name field...\n";
        
        $admin->update([
            'name' => trim($admin->first_name . ' ' . $admin->last_name)
        ]);
        
        $admin->refresh();
        echo "✓ Name updated to: " . $admin->name . "\n";
    }
} else {
    echo "✗ Admin user not found!\n";
}

echo "==========================================\n";
