<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Complaint;

class ComplaintNotesUpdatedNotification extends Notification
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
            'type' => 'complaint_notes_updated',
            'title' => 'Investigation Notes Updated',
            'message' => $staffName . ' updated investigation notes for complaint #' . $this->complaint->id . ': ' . $this->complaint->title,
            'complaint_id' => $this->complaint->id,
            'staff_name' => $staffName,
            'complaint_title' => $this->complaint->title,
            'action_url' => url('/admin/complaint-resources/' . $this->complaint->id),
            'icon' => 'heroicon-o-document-text',
            'color' => 'info'
        ];
    }
}