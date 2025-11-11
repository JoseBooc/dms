<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\UtilityReading;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillingService
{
    /**
     * Calculate total bill amount
     *
     * @param array $billData
     * @return float
     */
    public function calculateTotal(array $billData): float
    {
        $total = 0;
        $total += $billData['room_rate'] ?? 0;
        $total += $billData['electricity'] ?? 0;
        $total += $billData['water'] ?? 0;
        $total += $billData['other_charges'] ?? 0;
        $total += $billData['penalty_charge'] ?? 0;

        return round($total, 2);
    }

    /**
     * Get utility charges for a room and billing period
     *
     * @param int $roomId
     * @param Carbon $billingDate
     * @return array
     */
    public function getUtilityCharges(int $roomId, Carbon $billingDate): array
    {
        $startOfMonth = $billingDate->copy()->startOfMonth();
        $endOfMonth = $billingDate->copy()->endOfMonth();

        $readings = UtilityReading::where('room_id', $roomId)
            ->whereBetween('reading_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'verified')
            ->with('utilityType')
            ->get();

        $charges = [
            'electricity' => 0,
            'water' => 0,
        ];

        foreach ($readings as $reading) {
            $utilityName = strtolower($reading->utilityType->name);
            if (isset($charges[$utilityName])) {
                $charges[$utilityName] += $reading->amount;
            }
        }

        return $charges;
    }

    /**
     * Create a bill with auto-calculated totals (transaction-safe)
     *
     * @param array $billData
     * @return Bill
     */
    public function createBill(array $billData): Bill
    {
        return DB::transaction(function () use ($billData) {
            // Auto-calculate total if not provided
            if (!isset($billData['total_amount'])) {
                $billData['total_amount'] = $this->calculateTotal($billData);
            }

            // Set defaults
            $billData['status'] = $billData['status'] ?? 'unpaid';
            $billData['amount_paid'] = $billData['amount_paid'] ?? 0;
            $billData['bill_date'] = $billData['bill_date'] ?? now();
            
            // Default due date: 5 days after bill date
            if (!isset($billData['due_date'])) {
                $billData['due_date'] = Carbon::parse($billData['bill_date'])->addDays(5);
            }

            $billData['created_by'] = $billData['created_by'] ?? auth()->id();

            $bill = Bill::create($billData);

            Log::info('Bill created', [
                'bill_id' => $bill->id,
                'tenant_id' => $bill->tenant_id,
                'total_amount' => $bill->total_amount,
            ]);

            return $bill;
        });
    }

    /**
     * Update bill payment with transaction safety
     *
     * @param Bill $bill
     * @param float $paymentAmount
     * @return Bill
     */
    public function recordPayment(Bill $bill, float $paymentAmount): Bill
    {
        return DB::transaction(function () use ($bill, $paymentAmount) {
            $bill->amount_paid += $paymentAmount;

            // Update status based on payment
            $totalDue = $bill->total_amount + $bill->penalty_amount;
            
            if ($bill->amount_paid >= $totalDue) {
                $bill->status = 'paid';
            } elseif ($bill->amount_paid > 0) {
                $bill->status = 'partially_paid';
            } else {
                $bill->status = 'unpaid';
            }

            $bill->save();

            Log::info('Payment recorded', [
                'bill_id' => $bill->id,
                'payment_amount' => $paymentAmount,
                'new_status' => $bill->status,
                'amount_paid' => $bill->amount_paid,
            ]);

            return $bill;
        });
    }

    /**
     * Calculate balance remaining on a bill
     *
     * @param Bill $bill
     * @return float
     */
    public function calculateBalance(Bill $bill): float
    {
        $totalDue = $bill->total_amount + $bill->penalty_amount;
        return max(0, $totalDue - $bill->amount_paid);
    }

    /**
     * Check if a bill is overdue
     *
     * @param Bill $bill
     * @return bool
     */
    public function isOverdue(Bill $bill): bool
    {
        if ($bill->status === 'paid' || $bill->status === 'cancelled') {
            return false;
        }

        return now()->greaterThan($bill->due_date);
    }

    /**
     * Get days overdue for a bill
     *
     * @param Bill $bill
     * @return int
     */
    public function getDaysOverdue(Bill $bill): int
    {
        if (!$this->isOverdue($bill)) {
            return 0;
        }

        return now()->diffInDays($bill->due_date);
    }

    /**
     * Waive penalty on a bill
     *
     * @param Bill $bill
     * @param string $reason
     * @param int $waivedBy
     * @return Bill
     */
    public function waivePenalty(Bill $bill, string $reason, int $waivedBy): Bill
    {
        return DB::transaction(function () use ($bill, $reason, $waivedBy) {
            $bill->penalty_waived = true;
            $bill->penalty_waiver_reason = $reason;
            $bill->penalty_waived_by = $waivedBy;
            $bill->penalty_amount = 0;
            $bill->save();

            Log::info('Penalty waived', [
                'bill_id' => $bill->id,
                'waived_by' => $waivedBy,
                'reason' => $reason,
            ]);

            return $bill;
        });
    }

    /**
     * Get bill summary with all calculations
     *
     * @param Bill $bill
     * @return array
     */
    public function getBillSummary(Bill $bill): array
    {
        $totalDue = $bill->total_amount + $bill->penalty_amount;
        $balance = $this->calculateBalance($bill);

        return [
            'room_rate' => $bill->room_rate,
            'electricity' => $bill->electricity,
            'water' => $bill->water,
            'other_charges' => $bill->other_charges,
            'subtotal' => $bill->total_amount,
            'penalty_amount' => $bill->penalty_amount,
            'total_due' => $totalDue,
            'amount_paid' => $bill->amount_paid,
            'balance' => $balance,
            'status' => $bill->status,
            'is_overdue' => $this->isOverdue($bill),
            'days_overdue' => $this->getDaysOverdue($bill),
        ];
    }

    /**
     * Get unpaid bills for a tenant
     *
     * @param int $tenantId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnpaidBills(int $tenantId)
    {
        return Bill::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Calculate total outstanding balance for a tenant
     *
     * @param int $tenantId
     * @return float
     */
    public function getTotalOutstanding(int $tenantId): float
    {
        $unpaidBills = $this->getUnpaidBills($tenantId);
        $total = 0;

        foreach ($unpaidBills as $bill) {
            $total += $this->calculateBalance($bill);
        }

        return $total;
    }

    /**
     * Create a standalone penalty bill from an existing bill
     * Following Philippine dormitory billing practices
     *
     * @param Bill $originalBill The original bill that generated the penalty
     * @param float $amount The penalty amount to charge
     * @param string $reason The reason for the penalty bill
     * @return Bill The newly created penalty bill
     * @throws \Exception
     */
    public function createPenaltyBill(Bill $originalBill, float $amount, string $reason): Bill
    {
        return DB::transaction(function () use ($originalBill, $amount, $reason) {
            // Create the penalty bill
            $penaltyBill = Bill::create([
                'tenant_id' => $originalBill->tenant_id,
                'room_id' => $originalBill->room_id,
                'penalty_bill_for_id' => $originalBill->id,
                'bill_type' => 'penalty',
                'description' => "Penalty: {$reason}",
                'details' => [
                    'original_bill_id' => $originalBill->id,
                    'original_bill_number' => $originalBill->id,
                    'penalty_reason' => $reason,
                    'original_bill_type' => $originalBill->bill_type,
                    'original_bill_amount' => $originalBill->total_amount,
                    'days_overdue' => $originalBill->getDaysOverdue(),
                ],
                'created_by' => auth()->id(),
                'bill_date' => now(),
                'due_date' => now()->addDays(3), // 3-day grace period for penalty bills
                'room_rate' => 0,
                'electricity' => 0,
                'water' => 0,
                'other_charges' => $amount,
                'other_charges_description' => "Late payment penalty for Bill #{$originalBill->id}",
                'total_amount' => $amount,
                'status' => 'unpaid',
                'amount_paid' => 0,
            ]);

            // Create financial transaction entry using logBillCreated
            $financialService = app(FinancialTransactionService::class);
            $financialService->logBillCreated($penaltyBill);

            // Log audit trail with custom action
            $auditService = app(AuditLogService::class);
            $auditService->log(
                $penaltyBill,
                'penalty_bill_created',
                null,
                [
                    'original_bill_id' => $originalBill->id,
                    'penalty_amount' => $amount,
                    'reason' => $reason,
                    'original_bill_type' => $originalBill->bill_type,
                    'days_overdue' => $originalBill->getDaysOverdue(),
                ],
                "Penalty bill created for Bill #{$originalBill->id}: {$reason}"
            );

            Log::info('Penalty bill created', [
                'penalty_bill_id' => $penaltyBill->id,
                'original_bill_id' => $originalBill->id,
                'tenant_id' => $originalBill->tenant_id,
                'amount' => $amount,
                'reason' => $reason,
            ]);

            return $penaltyBill;
        });
    }
}
