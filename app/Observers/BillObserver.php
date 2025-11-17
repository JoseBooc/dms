<?php

namespace App\Observers;

use App\Models\Bill;
use App\Models\User;
use App\Notifications\PenaltyChargeNotification;
use App\Notifications\BillOverdueNotification;
use Illuminate\Support\Facades\Log;

class BillObserver
{
    /**
     * Handle the Bill "created" event.
     * When a bill is created, update associated utility readings to 'billed' status
     */
    public function created(Bill $bill): void
    {
        $this->updateUtilityReadingsStatus($bill);
    }

    /**
     * Handle the Bill "updated" event.
     * When a bill is updated (especially status changes), update utility readings accordingly
     */
    public function updated(Bill $bill): void
    {
        // Handle penalty charge notifications
        if ($bill->wasChanged('penalty_amount') && $bill->penalty_amount > 0) {
            $this->handlePenaltyCharge($bill);
        }

        // Handle overdue bill notifications
        if ($bill->wasChanged('status') && $bill->status === 'overdue') {
            $this->handleOverdueBill($bill);
        }

        // Only process utility reading status changes for bill status
        if (!$bill->wasChanged('status')) {
            return;
        }

        // Handle status changes based on bill payment status
        switch ($bill->status) {
            case 'paid':
                // Bill fully paid → mark utility readings as 'paid'
                $this->markUtilityReadingsAsPaid($bill);
                break;
                
            case 'partially_paid':
                // Bill partially paid → mark utility readings as 'partially_paid'
                $this->markUtilityReadingsAsPartiallyPaid($bill);
                break;
                
            case 'unpaid':
                // Bill unpaid → revert utility readings to 'billed' (or 'pending' if unlinked)
                $this->markUtilityReadingsAsUnpaid($bill);
                break;
        }
    }

    /**
     * Update utility readings status to 'billed' when bill is created
     */
    protected function updateUtilityReadingsStatus(Bill $bill): void
    {
        // Find utility readings for this tenant, room, and billing period that aren't already billed
        $utilityReadings = \App\Models\UtilityReading::where('tenant_id', $bill->tenant_id)
            ->where('room_id', $bill->room_id)
            ->whereNull('bill_id')
            ->where('status', 'pending')
            ->get();

        foreach ($utilityReadings as $reading) {
            $reading->update([
                'bill_id' => $bill->id,
                'status' => 'billed',
            ]);

            Log::info('Utility reading status updated to billed', [
                'reading_id' => $reading->id,
                'bill_id' => $bill->id,
                'tenant_id' => $bill->tenant_id,
            ]);
        }
    }

    /**
     * Mark utility readings as 'paid' when bill is fully paid
     */
    protected function markUtilityReadingsAsPaid(Bill $bill): void
    {
        $utilityReadings = $bill->utilityReadings()
            ->whereIn('status', ['billed', 'partially_paid'])
            ->get();

        foreach ($utilityReadings as $reading) {
            $reading->update(['status' => 'paid']);

            Log::info('Utility reading status updated to paid', [
                'reading_id' => $reading->id,
                'bill_id' => $bill->id,
                'tenant_id' => $bill->tenant_id,
                'previous_status' => $reading->getOriginal('status'),
            ]);
        }
    }

    /**
     * Mark utility readings as 'partially_paid' when bill is partially paid
     */
    protected function markUtilityReadingsAsPartiallyPaid(Bill $bill): void
    {
        $utilityReadings = $bill->utilityReadings()
            ->whereIn('status', ['billed', 'paid'])
            ->get();

        foreach ($utilityReadings as $reading) {
            $reading->update(['status' => 'partially_paid']);

            Log::info('Utility reading status updated to partially_paid', [
                'reading_id' => $reading->id,
                'bill_id' => $bill->id,
                'tenant_id' => $bill->tenant_id,
                'previous_status' => $reading->getOriginal('status'),
                'bill_amount_paid' => $bill->amount_paid,
                'bill_total' => $bill->total_amount,
            ]);
        }
    }

    /**
     * Mark utility readings as 'unpaid' when bill status changes to unpaid
     */
    protected function markUtilityReadingsAsUnpaid(Bill $bill): void
    {
        $utilityReadings = $bill->utilityReadings()
            ->whereIn('status', ['paid', 'partially_paid'])
            ->get();

        foreach ($utilityReadings as $reading) {
            // If bill is linked, mark as 'billed', otherwise 'pending'
            $newStatus = $bill->id ? 'billed' : 'pending';
            $reading->update(['status' => $newStatus]);

            Log::info('Utility reading status updated to ' . $newStatus, [
                'reading_id' => $reading->id,
                'bill_id' => $bill->id,
                'tenant_id' => $bill->tenant_id,
                'previous_status' => $reading->getOriginal('status'),
            ]);
        }
    }

    /**
     * Handle penalty charge notifications
     */
    protected function handlePenaltyCharge(Bill $bill): void
    {
        $tenant = User::find($bill->tenant_id);
        if ($tenant && $tenant->role === 'tenant') {
            $tenant->notify(new PenaltyChargeNotification($bill));
            
            Log::info('Penalty charge notification sent', [
                'bill_id' => $bill->id,
                'tenant_id' => $bill->tenant_id,
                'penalty_amount' => $bill->penalty_amount,
            ]);
        }
    }

    /**
     * Handle overdue bill notifications
     */
    protected function handleOverdueBill(Bill $bill): void
    {
        $tenant = User::find($bill->tenant_id);
        if ($tenant && $tenant->role === 'tenant') {
            $tenant->notify(new BillOverdueNotification($bill));
            
            Log::info('Overdue bill notification sent', [
                'bill_id' => $bill->id,
                'tenant_id' => $bill->tenant_id,
                'days_overdue' => $bill->overdue_days,
            ]);
        }
    }
}
