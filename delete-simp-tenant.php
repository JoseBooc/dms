<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Tenant;

echo "=== DELETING SIMP ARE DWDAD TENANT ===\n\n";

// Find tenant with user_id 6
$tenant = Tenant::where('user_id', 6)->first();

if (!$tenant) {
    echo "No tenant found for Simp Are dwdad.\n";
    exit(0);
}

$user = $tenant->user;

echo "Tenant ID: {$tenant->id}\n";
echo "User ID: {$tenant->user_id}\n";
echo "Name: {$tenant->first_name} {$tenant->last_name}\n";
echo "Email: " . ($user ? $user->email : 'N/A') . "\n\n";

try {
    // Delete tenant record
    $tenant->delete();
    echo "✓ Tenant record deleted\n";
    
    // Delete associated user
    if ($user) {
        $user->delete();
        echo "✓ User record deleted\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
}

echo "\n=== DELETION COMPLETE ===\n";
