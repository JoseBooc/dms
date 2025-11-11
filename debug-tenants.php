<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TENANT DEBUGGING ===\n\n";

$usersWithTenantRole = App\Models\User::where('role', 'tenant')->count();
$tenantRecords = App\Models\Tenant::count();

echo "Total Users with 'tenant' role: {$usersWithTenantRole}\n";
echo "Total Tenant records: {$tenantRecords}\n\n";

echo "=== USERS WITH TENANT ROLE ===\n";
$tenantUsers = App\Models\User::where('role', 'tenant')->get();
foreach ($tenantUsers as $user) {
    $hasTenantRecord = $user->tenant ? 'YES' : 'NO';
    echo "User ID: {$user->id} | Name: {$user->name} | Email: {$user->email} | Has Tenant Record: {$hasTenantRecord}\n";
}

echo "\n=== TENANT RECORDS ===\n";
$tenants = App\Models\Tenant::with('user')->get();
foreach ($tenants as $tenant) {
    $userName = $tenant->user ? $tenant->user->name : 'NO USER LINKED';
    echo "Tenant ID: {$tenant->id} | User ID: {$tenant->user_id} | Name: {$tenant->first_name} {$tenant->last_name} | Linked User: {$userName}\n";
}

echo "\n=== CHECKING FOR ORPHANED RECORDS ===\n";
$usersWithoutTenant = App\Models\User::where('role', 'tenant')
    ->doesntHave('tenant')
    ->get();

if ($usersWithoutTenant->count() > 0) {
    echo "Found {$usersWithoutTenant->count()} users with 'tenant' role but no tenant record:\n";
    foreach ($usersWithoutTenant as $user) {
        echo "  - User ID: {$user->id} | Name: {$user->name} | Email: {$user->email} | Created: {$user->created_at}\n";
    }
} else {
    echo "No orphaned users found.\n";
}
