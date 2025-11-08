<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TENANT ANALYTICS DATA TEST ===\n\n";

// Get all tenants
$tenants = \App\Models\Tenant::all();
echo "Total Tenants: " . $tenants->count() . "\n\n";

foreach ($tenants as $tenant) {
    echo "--- TENANT ID: {$tenant->id} ---\n";
    echo "Name: {$tenant->first_name} {$tenant->last_name}\n";
    echo "Email: {$tenant->email}\n";
    
    // Check bills for this tenant
    $bills = \App\Models\Bill::where('tenant_id', $tenant->id)->get();
    echo "Bills Count: " . $bills->count() . "\n";
    if ($bills->count() > 0) {
        echo "Bills: ";
        foreach ($bills as $bill) {
            echo "[ID: {$bill->id}, Amount: {$bill->total_amount}] ";
        }
        echo "\n";
        echo "Total Amount: " . $bills->sum('total_amount') . "\n";
        echo "Pending Amount: " . $bills->where('status', 'pending')->sum('total_amount') . "\n";
    }
    
    // Check complaints for this tenant
    $complaints = \App\Models\Complaint::where('tenant_id', $tenant->id)->get();
    echo "Complaints Count: " . $complaints->count() . "\n";
    if ($complaints->count() > 0) {
        echo "Complaints: ";
        foreach ($complaints as $complaint) {
            echo "[ID: {$complaint->id}, Status: {$complaint->status}] ";
        }
        echo "\n";
    }
    
    // Check maintenance requests for this tenant
    $maintenance = \App\Models\MaintenanceRequest::where('tenant_id', $tenant->id)->get();
    echo "Maintenance Requests Count: " . $maintenance->count() . "\n";
    if ($maintenance->count() > 0) {
        echo "Maintenance: ";
        foreach ($maintenance as $req) {
            echo "[ID: {$req->id}, Status: {$req->status}] ";
        }
        echo "\n";
    }
    
    echo "\n";
}

// Check all bills to see their tenant_id values
echo "=== ALL BILLS ===\n";
$allBills = \App\Models\Bill::all();
foreach ($allBills as $bill) {
    echo "Bill ID: {$bill->id}, Tenant ID: {$bill->tenant_id}, Amount: {$bill->total_amount}, Status: {$bill->status}\n";
}

echo "\n=== ALL COMPLAINTS ===\n";
$allComplaints = \App\Models\Complaint::all();
foreach ($allComplaints as $complaint) {
    echo "Complaint ID: {$complaint->id}, Tenant ID: {$complaint->tenant_id}, Status: {$complaint->status}\n";
}

echo "\n=== ALL MAINTENANCE REQUESTS ===\n";
$allMaintenance = \App\Models\MaintenanceRequest::all();
foreach ($allMaintenance as $req) {
    echo "Maintenance ID: {$req->id}, Tenant ID: {$req->tenant_id}, Status: {$req->status}\n";
}