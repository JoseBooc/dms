<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== FIXING ORPHANED DATA ===\n\n";

// Update bills with tenant_id 34 to use tenant_id 25 (Jernelle Test)
$billsUpdated = \App\Models\Bill::where('tenant_id', 34)->update(['tenant_id' => 25]);
echo "Updated {$billsUpdated} bills from tenant_id 34 to 25\n";

// Update bills with tenant_id 41 to use tenant_id 26 (Kendra Test)
$billsUpdated2 = \App\Models\Bill::where('tenant_id', 41)->update(['tenant_id' => 26]);
echo "Updated {$billsUpdated2} bills from tenant_id 41 to 26\n";

// Update complaints with tenant_id 34 to use tenant_id 25 (Jernelle Test)
$complaintsUpdated = \App\Models\Complaint::where('tenant_id', 34)->update(['tenant_id' => 25]);
echo "Updated {$complaintsUpdated} complaints from tenant_id 34 to 25\n";

// Update complaints with tenant_id 41 to use tenant_id 26 (Kendra Test)
$complaintsUpdated2 = \App\Models\Complaint::where('tenant_id', 41)->update(['tenant_id' => 26]);
echo "Updated {$complaintsUpdated2} complaints from tenant_id 41 to 26\n";

echo "\n=== VERIFICATION ===\n";

// Verify the updates
foreach ([25, 26] as $tenantId) {
    $tenant = \App\Models\Tenant::find($tenantId);
    $bills = \App\Models\Bill::where('tenant_id', $tenantId)->get();
    $complaints = \App\Models\Complaint::where('tenant_id', $tenantId)->get();
    
    echo "\nTenant ID {$tenantId} ({$tenant->first_name} {$tenant->last_name}):\n";
    echo "  Bills: " . $bills->count() . " (Total: " . $bills->sum('total_amount') . ")\n";
    echo "  Complaints: " . $complaints->count() . "\n";
}