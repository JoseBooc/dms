<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Bill;

class BillOverdueNotification extends Notification
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
            'type' => 'bill_overdue',
            'title' => 'Bill Overdue',
            'message' => 'Your bill #' . $this->bill->id . ' is now overdue. Please pay to avoid penalty charges.',
            'bill_id' => $this->bill->id,
            'total_amount' => $this->bill->total_amount,
            'due_date' => $this->bill->due_date->format('M d, Y'),
            'days_overdue' => now()->diffInDays($this->bill->due_date),
            'action_url' => url('/dashboard/tenant-bill-resources/' . $this->bill->id),
            'icon' => 'heroicon-o-exclamation-triangle',
            'color' => 'danger'
        ];
    }
}