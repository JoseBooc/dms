<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING ORPHANED DATA ===\n\n";

// Check if tenant IDs 34 and 41 exist
$tenant34 = \App\Models\Tenant::find(34);
$tenant41 = \App\Models\Tenant::find(41);

echo "Tenant ID 34 exists: " . ($tenant34 ? 'YES' : 'NO') . "\n";
echo "Tenant ID 41 exists: " . ($tenant41 ? 'YES' : 'NO') . "\n\n";

// Check all existing tenant IDs
echo "=== ALL EXISTING TENANT IDS ===\n";
$tenants = \App\Models\Tenant::all();
foreach ($tenants as $tenant) {
    echo "Tenant ID: {$tenant->id} - {$tenant->first_name} {$tenant->last_name}\n";
}

echo "\n=== BILLS WITH INVALID TENANT IDS ===\n";
$bills = \App\Models\Bill::whereNotIn('tenant_id', [25, 26])->get();
foreach ($bills as $bill) {
    echo "Bill ID: {$bill->id}, Invalid Tenant ID: {$bill->tenant_id}, Amount: {$bill->total_amount}\n";
}

echo "\n=== COMPLAINTS WITH INVALID TENANT IDS ===\n";
$complaints = \App\Models\Complaint::whereNotIn('tenant_id', [25, 26])->get();
foreach ($complaints as $complaint) {
    echo "Complaint ID: {$complaint->id}, Invalid Tenant ID: {$complaint->tenant_id}, Status: {$complaint->status}\n";
}