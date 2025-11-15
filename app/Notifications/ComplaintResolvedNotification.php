<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Complaint;

class ComplaintResolvedNotification extends Notification
{
    use Queueable;

    protected $complaint;

    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $staffName = $this->complaint->assignedTo->name ?? 'Staff member';
        
        return [
            'type' => 'complaint_resolved',
            'title' => 'Complaint Resolved',
            'message' => 'Complaint #' . $this->complaint->id . ' has been resolved: ' . $this->complaint->title,
            'complaint_id' => $this->complaint->id,
            'staff_name' => $staffName,
            'complaint_title' => $this->complaint->title,
            'actions_taken' => $this->complaint->actions_taken,
            'action_url' => url('/dashboard/tenant-complaint-resources/' . $this->complaint->id),
            'icon' => 'heroicon-o-check-circle',
            'color' => 'success'
        ];
    }
}