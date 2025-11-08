<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TENANT-USER RELATIONSHIP VERIFICATION ===\n\n";

// Get all tenants with their user relationships
$tenants = \App\Models\Tenant::with('user')->get();

foreach ($tenants as $tenant) {
    echo "--- TENANT ID: {$tenant->id} ---\n";
    echo "Name: {$tenant->first_name} {$tenant->last_name}\n";
    echo "User ID: {$tenant->user_id}\n";
    
    if ($tenant->user) {
        echo "Associated User: {$tenant->user->first_name} {$tenant->user->last_name} ({$tenant->user->email})\n";
        
        // Check bills for this user
        $bills = \App\Models\Bill::where('tenant_id', $tenant->user_id)->get();
        echo "Bills Count (using user_id): " . $bills->count() . "\n";
        if ($bills->count() > 0) {
            echo "Total Amount: ₱" . number_format($bills->sum('total_amount'), 2) . "\n";
        }
        
        // Check complaints for this user
        $complaints = \App\Models\Complaint::where('tenant_id', $tenant->user_id)->get();
        echo "Complaints Count (using user_id): " . $complaints->count() . "\n";
        
    } else {
        echo "No associated user found!\n";
    }
    
    // Check maintenance requests for this tenant
    $maintenance = \App\Models\MaintenanceRequest::where('tenant_id', $tenant->id)->get();
    echo "Maintenance Requests Count (using tenant_id): " . $maintenance->count() . "\n";
    
    echo "\n";
}

echo "=== ORPHANED DATA CHECK ===\n";
echo "Bills referencing non-existent users:\n";
$orphanedBills = \App\Models\Bill::whereNotIn('tenant_id', \App\Models\User::pluck('id'))->get();
foreach ($orphanedBills as $bill) {
    echo "  Bill ID: {$bill->id}, Invalid User ID: {$bill->tenant_id}, Amount: ₱{$bill->total_amount}\n";
}

echo "\nComplaints referencing non-existent users:\n";
$orphanedComplaints = \App\Models\Complaint::whereNotIn('tenant_id', \App\Models\User::pluck('id'))->get();
foreach ($orphanedComplaints as $complaint) {
    echo "  Complaint ID: {$complaint->id}, Invalid User ID: {$complaint->tenant_id}\n";
}