<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Deposit;

echo "=== RECALCULATING ALL DEPOSIT TOTALS ===\n\n";

$deposits = Deposit::all();

echo "Found {$deposits->count()} deposit(s) to recalculate.\n\n";

foreach ($deposits as $deposit) {
    $oldDeductionsTotal = $deposit->deductions_total;
    $oldRefundable = $deposit->refundable_amount;
    
    // Recalculate from active deductions
    $deposit->recalculateDeductionsTotal();
    
    echo "Deposit ID {$deposit->id}:\n";
    echo "  Deductions Total: ₱" . number_format($oldDeductionsTotal, 2) . " → ₱" . number_format($deposit->deductions_total, 2) . "\n";
    echo "  Refundable Amount: ₱" . number_format($oldRefundable, 2) . " → ₱" . number_format($deposit->refundable_amount, 2) . "\n";
    echo "  Active Deductions: " . $deposit->activeDeductions()->count() . "\n\n";
}

echo "✅ All deposits recalculated successfully!\n";
