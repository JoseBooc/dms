<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\PenaltySetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PenaltyService
{
    /**
     * Process penalties for all overdue bills
     */
    public function processOverdueBills(): array
    {
        $results = [
            'processed' => 0,
            'penalties_applied' => 0,
            'total_penalty_amount' => 0,
            'errors' => []
        ];

        try {
            $overdueBills = Bill::overdue()
                ->where('penalty_waived', false)
                ->get();

            $results['processed'] = $overdueBills->count();

            foreach ($overdueBills as $bill) {
                try {
                    $oldPenalty = $bill->penalty_amount;
                    $bill->calculatePenalty();
                    
                    if ($bill->penalty_amount > $oldPenalty) {
                        $results['penalties_applied']++;
                        $results['total_penalty_amount'] += ($bill->penalty_amount - $oldPenalty);
                        
                        Log::info("Penalty applied to Bill #{$bill->id}", [
                            'bill_id' => $bill->id,
                            'tenant_id' => $bill->tenant_id,
                            'old_penalty' => $oldPenalty,
                            'new_penalty' => $bill->penalty_amount,
                            'overdue_days' => $bill->getDaysOverdue()
                        ]);
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Bill #{$bill->id}: " . $e->getMessage();
                    Log::error("Error processing penalty for Bill #{$bill->id}", [
                        'error' => $e->getMessage(),
                        'bill_id' => $bill->id
                    ]);
                }
            }

        } catch (\Exception $e) {
            $results['errors'][] = "General error: " . $e->getMessage();
            Log::error("Error in penalty processing", ['error' => $e->getMessage()]);
        }

        return $results;
    }

    /**
     * Get bills eligible for penalty calculation
     */
    public function getEligibleBills(): Collection
    {
        return Bill::overdue()
            ->where('penalty_waived', false)
            ->with(['tenant', 'room'])
            ->get();
    }

    /**
     * Preview penalty calculation for a specific bill
     */
    public function previewPenalty(Bill $bill): array
    {
        if (!$bill->isOverdue() || $bill->penalty_waived) {
            return [
                'eligible' => false,
                'reason' => $bill->penalty_waived ? 'Penalty waived' : 'Bill not overdue',
                'current_penalty' => $bill->penalty_amount,
                'new_penalty' => $bill->penalty_amount,
                'increase' => 0
            ];
        }

        $penaltySetting = PenaltySetting::getActiveSetting('late_payment_penalty');
        if (!$penaltySetting) {
            return [
                'eligible' => false,
                'reason' => 'No penalty settings configured',
                'current_penalty' => $bill->penalty_amount,
                'new_penalty' => $bill->penalty_amount,
                'increase' => 0
            ];
        }

        $overdueDays = $bill->getDaysOverdue();
        $newPenalty = $penaltySetting->calculatePenalty($bill->total_amount, $overdueDays);

        return [
            'eligible' => true,
            'penalty_setting' => $penaltySetting,
            'overdue_days' => $overdueDays,
            'current_penalty' => $bill->penalty_amount,
            'new_penalty' => $newPenalty,
            'increase' => $newPenalty - $bill->penalty_amount,
            'grace_period' => $penaltySetting->grace_period_days,
            'penalty_rate' => $penaltySetting->penalty_rate,
            'penalty_type' => $penaltySetting->penalty_type,
            'max_penalty' => $penaltySetting->max_penalty_amount
        ];
    }

    /**
     * Waive penalty for multiple bills
     */
    public function waivePenaltiesForBills(array $billIds, string $reason, int $waivedBy): array
    {
        $results = [
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($billIds as $billId) {
            try {
                $bill = Bill::findOrFail($billId);
                
                if ($bill->waivePenalty($reason, $waivedBy)) {
                    $results['successful']++;
                    
                    Log::info("Penalty waived for Bill #{$bill->id}", [
                        'bill_id' => $bill->id,
                        'waived_by' => $waivedBy,
                        'reason' => $reason,
                        'penalty_amount' => $bill->penalty_amount
                    ]);
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to waive penalty for Bill #{$billId}";
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Bill #{$billId}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get penalty statistics
     */
    public function getPenaltyStatistics(): array
    {
        $totalBills = Bill::count();
        $overdueBills = Bill::overdue()->count();
        $billsWithPenalties = Bill::where('penalty_amount', '>', 0)->count();
        $waivedPenalties = Bill::where('penalty_waived', true)->count();
        $totalPenaltyAmount = Bill::sum('penalty_amount');
        $totalWaivedAmount = Bill::where('penalty_waived', true)->sum('penalty_amount');

        return [
            'total_bills' => $totalBills,
            'overdue_bills' => $overdueBills,
            'bills_with_penalties' => $billsWithPenalties,
            'waived_penalties' => $waivedPenalties,
            'total_penalty_amount' => $totalPenaltyAmount,
            'total_waived_amount' => $totalWaivedAmount,
            'active_penalty_amount' => $totalPenaltyAmount - $totalWaivedAmount,
            'overdue_percentage' => $totalBills > 0 ? round(($overdueBills / $totalBills) * 100, 2) : 0,
            'penalty_rate' => $overdueBills > 0 ? round(($billsWithPenalties / $overdueBills) * 100, 2) : 0
        ];
    }

    /**
     * Get penalty history for a specific tenant
     */
    public function getTenantPenaltyHistory(int $tenantId): Collection
    {
        return Bill::where('tenant_id', $tenantId)
            ->where('penalty_amount', '>', 0)
            ->with(['room', 'penaltyWaivedBy'])
            ->orderBy('penalty_applied_date', 'desc')
            ->get();
    }
}