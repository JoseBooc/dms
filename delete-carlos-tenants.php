<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Tenant;

echo "=== DELETING CARLOS GAY ACEBU TENANTS ===\n\n";

// Find tenants named Carlos Gay Acebu
$tenants = Tenant::whereIn('user_id', [8, 9])->get();

if ($tenants->count() === 0) {
    echo "No tenants found for Carlos Gay Acebu.\n";
    exit(0);
}

echo "Found {$tenants->count()} tenant record(s) for Carlos Gay Acebu:\n\n";

foreach ($tenants as $tenant) {
    $user = $tenant->user;
    
    echo "Tenant ID: {$tenant->id}\n";
    echo "User ID: {$tenant->user_id}\n";
    echo "Name: {$tenant->first_name} {$tenant->last_name}\n";
    echo "Email: " . ($user ? $user->email : 'N/A') . "\n";
    
    try {
        // Delete tenant record
        $tenant->delete();
        echo "✓ Tenant record deleted\n";
        
        // Delete associated user
        if ($user) {
            $user->delete();
            echo "✓ User record deleted\n";
        }
        
        echo "\n";
    } catch (\Exception $e) {
        echo "✗ Error: {$e->getMessage()}\n\n";
    }
}

echo "=== DELETION COMPLETE ===\n";
