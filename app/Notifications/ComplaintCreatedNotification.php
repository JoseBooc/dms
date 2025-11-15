<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Complaint;

class ComplaintCreatedNotification extends Notification
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
        return [
            'type' => 'complaint_created',
            'title' => 'New Complaint Submitted',
            'message' => 'New complaint from ' . $this->complaint->tenant->name . ': ' . $this->complaint->title,
            'complaint_id' => $this->complaint->id,
            'tenant_name' => $this->complaint->tenant->name,
            'complaint_title' => $this->complaint->title,
            'action_url' => url('/admin/complaint-resources/' . $this->complaint->id),
            'icon' => 'heroicon-o-exclamation-triangle',
            'color' => 'warning'
        ];
    }
}