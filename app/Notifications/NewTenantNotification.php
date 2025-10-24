<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Room;

class NewTenantNotification extends Notification
{
    use Queueable;

    protected $tenant;
    protected $room;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $tenant, Room $room = null)
    {
        $this->tenant = $tenant;
        $this->room = $room;
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
                    ->subject('New Tenant Registered')
                    ->greeting('Hello!')
                    ->line('A new tenant has been registered in the system.')
                    ->line('Tenant: ' . $this->tenant->name)
                    ->line('Email: ' . $this->tenant->email)
                    ->when($this->room, function ($mail) {
                        return $mail->line('Assigned Room: ' . $this->room->room_number);
                    })
                    ->action('View Tenants', url('/admin/tenants'))
                    ->line('Please review and welcome the new tenant!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_tenant',
            'title' => 'New Tenant Registered',
            'message' => $this->tenant->name . ' has been registered as a new tenant' . 
                        ($this->room ? ' in room ' . $this->room->room_number : ''),
            'tenant_id' => $this->tenant->id,
            'tenant_name' => $this->tenant->name,
            'tenant_email' => $this->tenant->email,
            'room_id' => $this->room?->id,
            'room_number' => $this->room?->room_number,
            'action_url' => url('/admin/tenants'),
            'icon' => 'heroicon-o-user',
            'color' => 'info'
        ];
    }
}
