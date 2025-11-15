<?php

namespace App\Observers;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\MaintenanceRequestCreatedNotification;
use App\Notifications\MaintenanceWorkStartedNotification;
use App\Notifications\MaintenanceWorkCompletedNotification;
use App\Notifications\StaffAssignmentNotification;
use Illuminate\Support\Facades\Notification;

class MaintenanceRequestObserver
{
    /**
     * Handle the MaintenanceRequest "created" event.
     */
    public function created(MaintenanceRequest $maintenanceRequest): void
    {
        // Notify all admin users about new maintenance request
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new MaintenanceRequestCreatedNotification($maintenanceRequest));
    }

    /**
     * Handle the MaintenanceRequest "updated" event.
     */
    public function updated(MaintenanceRequest $maintenanceRequest): void
    {
        // Handle status changes
        if ($maintenanceRequest->wasChanged('status')) {
            $this->handleStatusChange($maintenanceRequest);
        }

        // Handle assignment changes
        if ($maintenanceRequest->wasChanged('assigned_to')) {
            $this->handleAssignmentChange($maintenanceRequest);
        }
    }

    /**
     * Handle status changes for maintenance requests
     */
    private function handleStatusChange(MaintenanceRequest $maintenanceRequest): void
    {
        $tenant = $maintenanceRequest->tenant;
        $assignedStaff = $maintenanceRequest->assignee;

        switch ($maintenanceRequest->status) {
            case 'in_progress':
                // Notify tenant and assigned staff that work has started
                if ($tenant && $tenant->user) {
                    $tenant->user->notify(new MaintenanceWorkStartedNotification($maintenanceRequest));
                }
                if ($assignedStaff) {
                    $assignedStaff->notify(new MaintenanceWorkStartedNotification($maintenanceRequest));
                }
                break;

            case 'completed':
                // Notify tenant and assigned staff that work is completed
                if ($tenant && $tenant->user) {
                    $tenant->user->notify(new MaintenanceWorkCompletedNotification($maintenanceRequest));
                }
                if ($assignedStaff) {
                    $assignedStaff->notify(new MaintenanceWorkCompletedNotification($maintenanceRequest));
                }
                break;
        }
    }

    /**
     * Handle assignment changes for maintenance requests
     */
    private function handleAssignmentChange(MaintenanceRequest $maintenanceRequest): void
    {
        // Only notify if there's actually an assigned staff member
        if (!$maintenanceRequest->assigned_to) {
            return;
        }

        $assignedStaff = $maintenanceRequest->assignee;
        if (!$assignedStaff) {
            return;
        }

        // Determine if this is a new assignment or reassignment
        $originalAssignedTo = $maintenanceRequest->getOriginal('assigned_to');
        $assignmentType = $originalAssignedTo ? 'reassigned' : 'assigned';

        // Notify the newly assigned staff member
        $assignedStaff->notify(new StaffAssignmentNotification($maintenanceRequest, $assignmentType));
    }
}