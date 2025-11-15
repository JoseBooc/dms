<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\MaintenanceRequest;

class MaintenanceWorkCompletedNotification extends Notification
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
        $staffName = $this->maintenanceRequest->assignedTo->name ?? 'Staff member';
        
        return [
            'type' => 'maintenance_work_completed',
            'title' => 'Maintenance Work Completed',
            'message' => 'Maintenance request #' . $this->maintenanceRequest->id . ' has been completed by ' . $staffName,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'staff_name' => $staffName,
            'description' => $this->maintenanceRequest->description,
            'action_url' => url('/admin/maintenance-request-resources/' . $this->maintenanceRequest->id),
            'icon' => 'heroicon-o-check-circle',
            'color' => 'success'
        ];
    }
}