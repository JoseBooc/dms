<?php

namespace App\Observers;

use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintCreatedNotification;
use App\Notifications\ComplaintInvestigationStartedNotification;
use App\Notifications\ComplaintNotesUpdatedNotification;
use App\Notifications\ComplaintResolvedNotification;
use Illuminate\Support\Facades\Notification;

class ComplaintObserver
{
    /**
     * Handle the Complaint "created" event.
     */
    public function created(Complaint $complaint): void
    {
        // Notify all admin users about new complaint
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new ComplaintCreatedNotification($complaint));
    }

    /**
     * Handle the Complaint "updated" event.
     */
    public function updated(Complaint $complaint): void
    {
        // Handle status changes
        if ($complaint->wasChanged('status')) {
            $this->handleStatusChange($complaint);
        }

        // Handle staff notes updates
        if ($complaint->wasChanged('staff_notes') && !empty($complaint->staff_notes)) {
            $this->handleNotesUpdate($complaint);
        }

        // Handle assignment changes
        if ($complaint->wasChanged('assigned_to')) {
            $this->handleAssignmentChange($complaint);
        }
    }

    /**
     * Handle status changes for complaints
     */
    private function handleStatusChange(Complaint $complaint): void
    {
        // For complaints, tenant_id is directly the user_id
        $tenant = User::find($complaint->tenant_id);
        $assignedStaff = $complaint->assignedTo;

        switch ($complaint->status) {
            case 'investigating':
                // Notify tenant and assigned staff that investigation has started
                if ($tenant && $tenant->role === 'tenant') {
                    $tenant->notify(new ComplaintInvestigationStartedNotification($complaint));
                }
                if ($assignedStaff) {
                    $assignedStaff->notify(new ComplaintInvestigationStartedNotification($complaint));
                }
                break;

            case 'resolved':
                // Notify tenant and assigned staff that complaint is resolved
                if ($tenant) {
                    $tenant->notify(new ComplaintResolvedNotification($complaint));
                }
                if ($assignedStaff) {
                    $assignedStaff->notify(new ComplaintResolvedNotification($complaint));
                }
                break;
        }
    }

    /**
     * Handle staff notes updates
     */
    private function handleNotesUpdate(Complaint $complaint): void
    {
        // For complaints, tenant_id is directly the user_id
        $tenant = User::find($complaint->tenant_id);
        $assignedStaff = $complaint->assignedTo;

        // Notify tenant about notes update
        if ($tenant && $tenant->role === 'tenant') {
            $tenant->notify(new ComplaintNotesUpdatedNotification($complaint));
        }

        // Also notify assigned staff if it's a different person
        if ($assignedStaff && $assignedStaff->id !== auth()->id()) {
            $assignedStaff->notify(new ComplaintNotesUpdatedNotification($complaint));
        }
    }

    /**
     * Handle assignment changes for complaints
     */
    private function handleAssignmentChange(Complaint $complaint): void
    {
        // When a complaint gets assigned, auto-change status to investigating if it's still open
        if ($complaint->assigned_to && $complaint->status === 'open') {
            $complaint->update(['status' => 'investigating']);
        }
    }
}