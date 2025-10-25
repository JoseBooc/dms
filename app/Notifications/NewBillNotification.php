<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Bill;

class NewBillNotification extends Notification
{
    use Queueable;

    protected $bill;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New Bill Generated')
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('A new bill has been generated for your account.')
                    ->line('Amount: ₱' . number_format($this->bill->total_amount, 2))
                    ->line('Due Date: ' . $this->bill->due_date->format('M j, Y'))
                    ->action('View Bill', url('/tenant/bills'))
                    ->line('Please pay your bill on or before the due date.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_bill',
            'title' => 'New Bill Generated',
            'message' => 'A new bill of ₱' . number_format($this->bill->total_amount, 2) . ' has been generated.',
            'bill_id' => $this->bill->id,
            'amount' => $this->bill->total_amount,
            'due_date' => $this->bill->due_date->toDateString(),
            'action_url' => url('/tenant/bills'),
            'icon' => 'heroicon-o-document',
            'color' => 'warning'
        ];
    }
}
