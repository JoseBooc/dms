<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Bill;

class PaymentConfirmationNotification extends Notification
{
    use Queueable;

    protected $bill;
    protected $paidAmount;

    /**
     * Create a new notification instance.
     */
    public function __construct(Bill $bill, $paidAmount)
    {
        $this->bill = $bill;
        $this->paidAmount = $paidAmount;
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
                    ->subject('Payment Received')
                    ->greeting('Hello!')
                    ->line('Payment confirmation for Bill #' . $this->bill->id)
                    ->line('Amount Paid: ₱' . number_format($this->paidAmount, 2))
                    ->line('Remaining Balance: ₱' . number_format($this->bill->total_amount - $this->bill->amount_paid, 2))
                    ->action('View Bills', url('/admin/bills'))
                    ->line('Thank you for your business!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_confirmation',
            'title' => 'Payment Received',
            'message' => 'Payment of ₱' . number_format($this->paidAmount, 2) . ' received for Bill #' . $this->bill->id,
            'bill_id' => $this->bill->id,
            'paid_amount' => $this->paidAmount,
            'remaining_balance' => $this->bill->total_amount - $this->bill->amount_paid,
            'action_url' => url('/admin/bills'),
            'icon' => 'heroicon-o-cash',
            'color' => 'success'
        ];
    }
}
