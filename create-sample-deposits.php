<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CREATING SAMPLE DEPOSIT DATA ===\n\n";

// Get tenants and their room assignments
$tenants = \App\Models\Tenant::with('user')->get();

foreach ($tenants as $tenant) {
    echo "Processing Tenant: {$tenant->first_name} {$tenant->last_name} (User ID: {$tenant->user_id})\n";
    
    // Get the tenant's active room assignment
    $assignment = \App\Models\RoomAssignment::where('tenant_id', $tenant->id)
        ->where('status', 'active')
        ->first();
    
    if (!$assignment) {
        echo "  No active room assignment found, skipping...\n\n";
        continue;
    }
    
    // Check if deposit already exists
    $existingDeposit = \App\Models\Deposit::where('tenant_id', $tenant->user_id)
        ->where('room_assignment_id', $assignment->id)
        ->first();
    
    if ($existingDeposit) {
        echo "  Deposit already exists (₱{$existingDeposit->amount}), skipping...\n\n";
        continue;
    }
    
    // Create a deposit
    $depositAmount = 5000.00; // Standard deposit amount
    
    $deposit = \App\Models\Deposit::create([
        'tenant_id' => $tenant->user_id,
        'room_assignment_id' => $assignment->id,
        'amount' => $depositAmount,
        'deductions_total' => 0,
        'refundable_amount' => $depositAmount,
        'status' => 'active',
        'collected_date' => $assignment->start_date ?? now()->toDateString(),
        'notes' => 'Sample deposit for testing',
        'collected_by' => 1, // Assuming admin user ID is 1
    ]);
    
    echo "  ✓ Created deposit: ₱{$depositAmount}\n";
    echo "  Room: {$assignment->room->room_number}\n";
    echo "  Assignment ID: {$assignment->id}\n\n";
}

echo "=== DEPOSIT DATA CREATION COMPLETE ===\n";

// Show summary
$totalDeposits = \App\Models\Deposit::count();
$totalAmount = \App\Models\Deposit::sum('amount');

echo "\nSUMMARY:\n";
echo "Total Deposits Created: {$totalDeposits}\n";
echo "Total Amount: ₱" . number_format($totalAmount, 2) . "\n";