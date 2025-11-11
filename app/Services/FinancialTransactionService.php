<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Tenant;
use App\Models\Bill;
use App\Models\Deposit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinancialTransactionService
{
    /**
     * Log a bill creation transaction
     *
     * @param Bill $bill
     * @return FinancialTransaction
     */
    public function logBillCreated(Bill $bill): FinancialTransaction
    {
        return $this->logTransaction([
            'tenant_id' => $bill->tenant_id_new ?? $this->getTenantIdFromUser($bill->tenant_id),
            'type' => 'bill_created',
            'reference_type' => Bill::class,
            'reference_id' => $bill->id,
            'amount' => $bill->total_amount,
            'description' => "Bill #{$bill->id} created for {$bill->bill_date->format('M Y')}",
            'metadata' => [
                'room_rate' => $bill->room_rate,
                'electricity' => $bill->electricity,
                'water' => $bill->water,
                'other_charges' => $bill->other_charges,
            ],
        ]);
    }

    /**
     * Log a bill payment transaction
     *
     * @param Bill $bill
     * @param float $paymentAmount
     * @return FinancialTransaction
     */
    public function logBillPayment(Bill $bill, float $paymentAmount): FinancialTransaction
    {
        return $this->logTransaction([
            'tenant_id' => $bill->tenant_id_new ?? $this->getTenantIdFromUser($bill->tenant_id),
            'type' => 'bill_payment',
            'reference_type' => Bill::class,
            'reference_id' => $bill->id,
            'amount' => -$paymentAmount, // Negative for payments
            'description' => "Payment of ₱" . number_format($paymentAmount, 2) . " for Bill #{$bill->id}",
            'metadata' => [
                'payment_amount' => $paymentAmount,
                'new_amount_paid' => $bill->amount_paid,
                'bill_status' => $bill->status,
            ],
        ]);
    }

    /**
     * Log a penalty applied transaction
     *
     * @param Bill $bill
     * @param float $penaltyAmount
     * @return FinancialTransaction
     */
    public function logPenaltyApplied(Bill $bill, float $penaltyAmount): FinancialTransaction
    {
        return $this->logTransaction([
            'tenant_id' => $bill->tenant_id_new ?? $this->getTenantIdFromUser($bill->tenant_id),
            'type' => 'penalty_applied',
            'reference_type' => Bill::class,
            'reference_id' => $bill->id,
            'amount' => $penaltyAmount,
            'description' => "Penalty of ₱" . number_format($penaltyAmount, 2) . " applied to Bill #{$bill->id}",
            'metadata' => [
                'penalty_amount' => $penaltyAmount,
                'overdue_days' => $bill->overdue_days,
            ],
        ]);
    }

    /**
     * Log a penalty waived transaction
     *
     * @param Bill $bill
     * @param float $waivedAmount
     * @param string $reason
     * @return FinancialTransaction
     */
    public function logPenaltyWaived(Bill $bill, float $waivedAmount, string $reason): FinancialTransaction
    {
        return $this->logTransaction([
            'tenant_id' => $bill->tenant_id_new ?? $this->getTenantIdFromUser($bill->tenant_id),
            'type' => 'penalty_waived',
            'reference_type' => Bill::class,
            'reference_id' => $bill->id,
            'amount' => -$waivedAmount, // Negative for waiver
            'description' => "Penalty of ₱" . number_format($waivedAmount, 2) . " waived for Bill #{$bill->id}",
            'metadata' => [
                'waived_amount' => $waivedAmount,
                'reason' => $reason,
            ],
        ]);
    }

    /**
     * Log a deposit collected transaction
     *
     * @param Deposit $deposit
     * @return FinancialTransaction
     */
    public function logDepositCollected(Deposit $deposit): FinancialTransaction
    {
        return $this->logTransaction([
            'tenant_id' => $deposit->tenant_id_new ?? $this->getTenantIdFromUser($deposit->tenant_id),
            'type' => 'deposit_collected',
            'reference_type' => Deposit::class,
            'reference_id' => $deposit->id,
            'amount' => -$deposit->amount, // Negative (tenant paid)
            'description' => "Deposit of ₱" . number_format($deposit->amount, 2) . " collected",
            'metadata' => [
                'deposit_amount' => $deposit->amount,
                'collected_date' => $deposit->collected_date,
            ],
        ]);
    }

    /**
     * Log a deposit deduction transaction
     *
     * @param \App\Models\DepositDeduction $deduction
     * @return FinancialTransaction
     */
    public function logDepositDeduction($deduction): FinancialTransaction
    {
        return $this->logTransaction([
            'tenant_id' => $deduction->deposit->tenant_id_new ?? $this->getTenantIdFromUser($deduction->deposit->tenant_id),
            'type' => 'deposit_deduction',
            'reference_type' => get_class($deduction),
            'reference_id' => $deduction->id,
            'amount' => $deduction->amount,
            'description' => "Deposit deduction: {$deduction->description}",
            'metadata' => [
                'deduction_type' => $deduction->deduction_type,
                'deduction_amount' => $deduction->amount,
                'deposit_id' => $deduction->deposit_id,
            ],
        ]);
    }

    /**
     * Log a deposit refund transaction
     *
     * @param Deposit $deposit
     * @param float $refundAmount
     * @return FinancialTransaction
     */
    public function logDepositRefund(Deposit $deposit, float $refundAmount): FinancialTransaction
    {
        return $this->logTransaction([
            'tenant_id' => $deposit->tenant_id_new ?? $this->getTenantIdFromUser($deposit->tenant_id),
            'type' => 'deposit_refund',
            'reference_type' => Deposit::class,
            'reference_id' => $deposit->id,
            'amount' => -$refundAmount, // Negative (money returned to tenant)
            'description' => "Deposit refund of ₱" . number_format($refundAmount, 2),
            'metadata' => [
                'original_deposit' => $deposit->amount,
                'deductions_total' => $deposit->deductions_total,
                'refund_amount' => $refundAmount,
                'refund_date' => $deposit->refund_date,
            ],
        ]);
    }

    /**
     * Create a financial transaction with running balance calculation
     *
     * @param array $data
     * @return FinancialTransaction
     */
    private function logTransaction(array $data): FinancialTransaction
    {
        return DB::transaction(function () use ($data) {
            // Calculate running balance
            $lastTransaction = FinancialTransaction::where('tenant_id', $data['tenant_id'])
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $previousBalance = $lastTransaction ? $lastTransaction->running_balance : 0;
            $data['running_balance'] = $previousBalance + $data['amount'];

            // Set created_by if not provided
            if (!isset($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }

            return FinancialTransaction::create($data);
        });
    }

    /**
     * Get financial ledger for a tenant
     *
     * @param int $tenantId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLedger(int $tenantId, int $limit = 50)
    {
        return FinancialTransaction::where('tenant_id', $tenantId)
            ->with(['createdBy', 'reference'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get tenant's current balance
     *
     * @param int $tenantId
     * @return float
     */
    public function getCurrentBalance(int $tenantId): float
    {
        $lastTransaction = FinancialTransaction::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->running_balance : 0;
    }

    /**
     * Get financial summary for a tenant
     *
     * @param int $tenantId
     * @param \Carbon\Carbon|null $startDate
     * @param \Carbon\Carbon|null $endDate
     * @return array
     */
    public function getTenantSummary(int $tenantId, $startDate = null, $endDate = null): array
    {
        $query = FinancialTransaction::where('tenant_id', $tenantId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $transactions = $query->get();

        $summary = [
            'total_charges' => 0,
            'total_payments' => 0,
            'total_penalties' => 0,
            'total_deductions' => 0,
            'current_balance' => $this->getCurrentBalance($tenantId),
            'transactions_count' => $transactions->count(),
        ];

        foreach ($transactions as $transaction) {
            switch ($transaction->type) {
                case 'bill_created':
                    $summary['total_charges'] += $transaction->amount;
                    break;
                case 'bill_payment':
                    $summary['total_payments'] += abs($transaction->amount);
                    break;
                case 'penalty_applied':
                    $summary['total_penalties'] += $transaction->amount;
                    break;
                case 'deposit_deduction':
                    $summary['total_deductions'] += $transaction->amount;
                    break;
            }
        }

        return $summary;
    }

    /**
     * Helper to get tenant_id from user_id (during migration period)
     *
     * @param int $userId
     * @return int|null
     */
    private function getTenantIdFromUser(int $userId): ?int
    {
        return DB::table('tenants')->where('user_id', $userId)->value('id');
    }
}
