<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\MaintenanceRequest;

class MaintenanceRequestCreatedNotification extends Notification
{
    use Queueable;

    protected $maintenanceRequest;

    public function __construct(MaintenanceRequest $maintenanceRequest)
    {
        $this->maintenanceRequest = $maintenanceRequest;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'maintenance_request_created',
            'title' => 'New Maintenance Request',
            'message' => 'New maintenance request from ' . $this->maintenanceRequest->tenant->user->name . ' for ' . $this->maintenanceRequest->room->room_number,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'tenant_name' => $this->maintenanceRequest->tenant->user->name,
            'room_number' => $this->maintenanceRequest->room->room_number,
            'action_url' => url('/admin/maintenance-request-resources/' . $this->maintenanceRequest->id),
            'icon' => 'heroicon-o-cog',
            'color' => 'warning'
        ];
    }
}