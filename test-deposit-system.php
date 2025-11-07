<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING DEPOSIT SYSTEM ===\n\n";

// Get all deposits
$deposits = \App\Models\Deposit::with(['tenant', 'roomAssignment.room', 'deductions'])->get();

foreach ($deposits as $deposit) {
    echo "--- DEPOSIT FOR {$deposit->tenant->first_name} {$deposit->tenant->last_name} ---\n";
    echo "Amount: ₱" . number_format($deposit->amount, 2) . "\n";
    echo "Deductions: ₱" . number_format($deposit->deductions_total, 2) . "\n";
    echo "Refundable: ₱" . number_format($deposit->refundable_amount, 2) . "\n";
    echo "Status: {$deposit->status}\n";
    echo "Room: {$deposit->roomAssignment->room->room_number}\n";
    
    // Show related bills
    $bills = \App\Models\Bill::where('tenant_id', $deposit->tenant_id)->get();
    echo "Related Bills: " . $bills->count() . "\n";
    
    foreach ($bills as $bill) {
        $balance = $bill->getBalance();
        echo "  - Bill #{$bill->id}: ₱" . number_format($bill->total_amount, 2) . 
             " (Paid: ₱" . number_format($bill->amount_paid, 2) . 
             ", Balance: ₱" . number_format($balance, 2) . ") - {$bill->status}\n";
    }
    
    // Show deductions
    if ($deposit->deductions->count() > 0) {
        echo "Deductions:\n";
        foreach ($deposit->deductions as $deduction) {
            echo "  - {$deduction->getDeductionTypeLabel()}: ₱" . number_format($deduction->amount, 2) . 
                 " - {$deduction->description}\n";
        }
    }
    
    echo "\n";
}

echo "=== TESTING DEPOSIT DEDUCTION FUNCTIONALITY ===\n\n";

// Test adding a deduction to the first deposit
$firstDeposit = $deposits->first();
if ($firstDeposit && $firstDeposit->canBeRefunded()) {
    echo "Testing deduction on deposit for {$firstDeposit->tenant->first_name}...\n";
    
    // Get an unpaid bill for this tenant
    $unpaidBill = \App\Models\Bill::where('tenant_id', $firstDeposit->tenant_id)
        ->where('status', '!=', 'paid')
        ->first();
    
    if ($unpaidBill) {
        $balance = $unpaidBill->getBalance();
        $deductionAmount = min(1000, $balance, $firstDeposit->refundable_amount);
        
        echo "Found unpaid bill #{$unpaidBill->id} with balance ₱" . number_format($balance, 2) . "\n";
        echo "Adding deduction of ₱" . number_format($deductionAmount, 2) . "\n";
        
        try {
            $deduction = $firstDeposit->addDeduction(
                $deductionAmount,
                'unpaid_rent',
                'Test deduction for Bill #' . $unpaidBill->id,
                $unpaidBill->id,
                'This is a test deduction to verify the system works'
            );
            
            echo "✓ Deduction added successfully!\n";
            echo "  Deposit refundable amount: ₱" . number_format($firstDeposit->refundable_amount, 2) . "\n";
            echo "  Deposit status: {$firstDeposit->status}\n";
            
        } catch (\Exception $e) {
            echo "✗ Error adding deduction: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No unpaid bills found for this tenant.\n";
    }
} else {
    echo "No suitable deposit found for testing deduction.\n";
}

echo "\n=== DEPOSIT SYSTEM TEST COMPLETE ===\n";