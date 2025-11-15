<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\MaintenanceRequest;

class StaffAssignmentNotification extends Notification
{
    use Queueable;

    protected $maintenanceRequest;
    protected $assignmentType;

    public function __construct(MaintenanceRequest $maintenanceRequest, string $assignmentType = 'assigned')
    {
        $this->maintenanceRequest = $maintenanceRequest;
        $this->assignmentType = $assignmentType; // 'assigned' or 'reassigned'
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actionText = $this->assignmentType === 'reassigned' ? 'reassigned to you' : 'assigned to you';
        
        return [
            'type' => 'staff_assignment',
            'title' => 'Maintenance Request ' . ucfirst($this->assignmentType),
            'message' => 'Maintenance request #' . $this->maintenanceRequest->id . ' from Room ' . ($this->maintenanceRequest->room->room_number ?? 'N/A') . ' has been ' . $actionText,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'room_number' => $this->maintenanceRequest->room->room_number ?? null,
            'category' => $this->maintenanceRequest->category,
            'priority' => $this->maintenanceRequest->priority,
            'assignment_type' => $this->assignmentType,
            'action_url' => url('/dashboard/maintenance-request-resources/' . $this->maintenanceRequest->id),
            'icon' => 'heroicon-o-clipboard-list',
            'color' => 'info'
        ];
    }
}