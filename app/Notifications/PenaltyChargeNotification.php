<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Bill;

class PenaltyChargeNotification extends Notification
{
    use Queueable;

    protected $bill;

    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'penalty_charge',
            'title' => 'Penalty Charge Applied',
            'message' => 'A penalty charge of ₱' . number_format($this->bill->penalty_amount, 2) . ' has been applied to bill #' . $this->bill->id,
            'bill_id' => $this->bill->id,
            'penalty_amount' => $this->bill->penalty_amount,
            'total_amount' => $this->bill->total_amount,
            'action_url' => url('/dashboard/tenant-bill-resources/' . $this->bill->id),
            'icon' => 'heroicon-o-exclamation-triangle',
            'color' => 'danger'
        ];
    }
}