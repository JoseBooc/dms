<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\MaintenanceRequest;

class MaintenanceRequestNotification extends Notification
{
    use Queueable;

    protected $maintenanceRequest;
    protected $type; // 'new', 'update', 'completed'

    /**
     * Create a new notification instance.
     */
    public function __construct(MaintenanceRequest $maintenanceRequest, $type = 'new')
    {
        $this->maintenanceRequest = $maintenanceRequest;
        $this->type = $type;
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
        $subject = $this->getSubject();
        $message = $this->getMessage();
        
        return (new MailMessage)
                    ->subject($subject)
                    ->greeting('Hello!')
                    ->line($message)
                    ->line('Request: ' . $this->maintenanceRequest->title)
                    ->line('Priority: ' . ucfirst($this->maintenanceRequest->priority))
                    ->action('View Request', url('/admin/maintenance-requests'))
                    ->line('Thank you for your attention!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'maintenance_' . $this->type,
            'title' => $this->getSubject(),
            'message' => $this->getMessage(),
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'request_title' => $this->maintenanceRequest->title,
            'priority' => $this->maintenanceRequest->priority,
            'status' => $this->maintenanceRequest->status,
            'action_url' => url('/admin/maintenance-requests'),
            'icon' => $this->getIcon(),
            'color' => $this->getColor()
        ];
    }

    private function getSubject()
    {
        switch ($this->type) {
            case 'new':
                return 'New Maintenance Request';
            case 'update':
                return 'Maintenance Request Updated';
            case 'completed':
                return 'Maintenance Request Completed';
            default:
                return 'Maintenance Request Notification';
        }
    }

    private function getMessage()
    {
        switch ($this->type) {
            case 'new':
                return 'A new maintenance request has been submitted for ' . $this->maintenanceRequest->room->room_number;
            case 'update':
                return 'Maintenance request #' . $this->maintenanceRequest->id . ' status updated to ' . $this->maintenanceRequest->status;
            case 'completed':
                return 'Maintenance request #' . $this->maintenanceRequest->id . ' has been completed';
            default:
                return 'Maintenance request notification';
        }
    }

    private function getIcon()
    {
        switch ($this->type) {
            case 'new':
                return 'heroicon-o-cog';
            case 'update':
                return 'heroicon-o-arrow-path';
            case 'completed':
                return 'heroicon-o-check-circle';
            default:
                return 'heroicon-o-cog';
        }
    }

    private function getColor()
    {
        switch ($this->type) {
            case 'new':
                return 'warning';
            case 'update':
                return 'info';
            case 'completed':
                return 'success';
            default:
                return 'gray';
        }
    }
}
