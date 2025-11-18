<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\DepositDeduction;
use App\Models\Bill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepositService
{
    /**
     * Calculate refundable amount for a deposit
     *
     * @param Deposit $deposit
     * @return float
     */
    public function calculateRefundable(Deposit $deposit): float
    {
        $activeDeductionsTotal = $deposit->activeDeductions()->sum('amount');
        return max(0, $deposit->amount - $activeDeductionsTotal);
    }

    /**
     * Add a deduction to a deposit with transaction safety
     *
     * @param Deposit $deposit
     * @param array $deductionData
     * @return DepositDeduction
     * @throws \Exception
     */
    public function addDeduction(Deposit $deposit, array $deductionData): DepositDeduction
    {
        return DB::transaction(function () use ($deposit, $deductionData) {
            // Create deduction
            $deduction = $deposit->deductions()->create([
                'deduction_type' => $deductionData['deduction_type'],
                'amount' => $deductionData['amount'],
                'description' => $deductionData['description'] ?? null,
                'details' => $deductionData['details'] ?? null,
                'deduction_date' => $deductionData['deduction_date'] ?? now(),
                'processed_by' => $deductionData['processed_by'] ?? auth()->id(),
                'bill_id' => $deductionData['bill_id'] ?? null,
            ]);

            // Recalculate deposit totals
            $this->recalculateDeposit($deposit);

            Log::info('Deposit deduction added', [
                'deposit_id' => $deposit->id,
                'deduction_id' => $deduction->id,
                'amount' => $deduction->amount,
                'type' => $deduction->deduction_type,
            ]);

            return $deduction;
        });
    }

    /**
     * Archive (soft delete) a deduction with transaction safety
     *
     * @param DepositDeduction $deduction
     * @return bool
     */
    public function archiveDeduction(DepositDeduction $deduction): bool
    {
        return DB::transaction(function () use ($deduction) {
            $deposit = $deduction->deposit;
            
            // Soft delete the deduction
            $deduction->delete();

            // Recalculate deposit totals
            $this->recalculateDeposit($deposit);

            Log::info('Deposit deduction archived', [
                'deposit_id' => $deposit->id,
                'deduction_id' => $deduction->id,
                'amount' => $deduction->amount,
            ]);

            return true;
        });
    }

    /**
     * Restore an archived deduction with transaction safety
     *
     * @param DepositDeduction $deduction
     * @return bool
     */
    public function restoreDeduction(DepositDeduction $deduction): bool
    {
        return DB::transaction(function () use ($deduction) {
            $deposit = $deduction->deposit;
            
            // Restore the deduction
            $deduction->restore();

            // Recalculate deposit totals
            $this->recalculateDeposit($deposit);

            Log::info('Deposit deduction restored', [
                'deposit_id' => $deposit->id,
                'deduction_id' => $deduction->id,
                'amount' => $deduction->amount,
            ]);

            return true;
        });
    }

    /**
     * Recalculate deposit totals (deductions and refundable amount)
     *
     * @param Deposit $deposit
     * @return void
     */
    public function recalculateDeposit(Deposit $deposit): void
    {
        $deposit->deductions_total = $deposit->activeDeductions()->sum('amount');
        $deposit->refundable_amount = $this->calculateRefundable($deposit);
        $deposit->save();
    }

    /**
     * Process deposit refund with transaction safety
     *
     * @param Deposit $deposit
     * @param array $refundData
     * @return Deposit
     * @throws \Exception
     */
    public function processRefund(Deposit $deposit, array $refundData): Deposit
    {
        return DB::transaction(function () use ($deposit, $refundData) {
            // Validate refund amount
            if ($refundData['refund_amount'] > $deposit->refundable_amount) {
                throw new \Exception('Refund amount cannot exceed refundable amount');
            }

            // Update deposit status
            if ($refundData['refund_amount'] >= $deposit->refundable_amount) {
                $deposit->status = 'fully_refunded';
            } else {
                $deposit->status = 'partially_refunded';
            }

            $deposit->refunded_amount = ($deposit->refunded_amount ?? 0) + $refundData['refund_amount'];
            $deposit->refund_method = $refundData['refund_method'] ?? 'cash';
            $deposit->reference_number = $refundData['reference_number'] ?? null;
            $deposit->refund_notes = $refundData['refund_notes'] ?? null;
            $deposit->refunded_at = $refundData['refund_date'] ?? now();
            $deposit->save();

            Log::info('Deposit refund processed', [
                'deposit_id' => $deposit->id,
                'refund_amount' => $refundData['refund_amount'],
                'status' => $deposit->status,
            ]);

            return $deposit;
        });
    }

    /**
     * Auto-deduct unpaid bills from deposit during move-out
     *
     * @param Deposit $deposit
     * @param int $tenantId
     * @return array
     */
    public function autoDeductUnpaidBills(Deposit $deposit, int $tenantId): array
    {
        return DB::transaction(function () use ($deposit, $tenantId) {
            $unpaidBills = Bill::where('tenant_id', $tenantId)
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->get();

            $deductions = [];
            $totalDeducted = 0;

            foreach ($unpaidBills as $bill) {
                $unpaidAmount = $bill->total_amount + $bill->penalty_amount - $bill->amount_paid;

                if ($unpaidAmount > 0) {
                    $deduction = $this->addDeduction($deposit, [
                        'bill_id' => $bill->id,
                        'deduction_type' => $this->getDeductionTypeForBill($bill),
                        'amount' => $unpaidAmount,
                        'description' => "Unpaid bill #{$bill->id} for " . $bill->bill_date->format('M Y'),
                        'details' => "Auto-deducted during move-out",
                        'processed_by' => auth()->id(),
                    ]);

                    $deductions[] = $deduction;
                    $totalDeducted += $unpaidAmount;
                }
            }

            return [
                'deductions' => $deductions,
                'total_deducted' => $totalDeducted,
                'bills_count' => count($deductions),
            ];
        });
    }

    /**
     * Get appropriate deduction type based on bill composition
     *
     * @param Bill $bill
     * @return string
     */
    private function getDeductionTypeForBill(Bill $bill): string
    {
        // Prioritize largest component
        $components = [
            'unpaid_rent' => $bill->room_rate,
            'unpaid_electricity' => $bill->electricity,
            'unpaid_water' => $bill->water,
        ];

        if ($bill->penalty_amount > 0) {
            $components['penalty'] = $bill->penalty_amount;
        }

        arsort($components);
        return array_key_first($components);
    }

    /**
     * Get deposit summary with all calculations
     *
     * @param Deposit $deposit
     * @return array
     */
    public function getDepositSummary(Deposit $deposit): array
    {
        return [
            'deposit_amount' => $deposit->amount,
            'active_deductions' => $deposit->activeDeductions()->sum('amount'),
            'archived_deductions' => $deposit->archivedDeductions()->sum('amount'),
            'refundable_amount' => $this->calculateRefundable($deposit),
            'deductions_count' => $deposit->activeDeductions()->count(),
            'archived_count' => $deposit->archivedDeductions()->count(),
            'status' => $deposit->status,
        ];
    }
}
