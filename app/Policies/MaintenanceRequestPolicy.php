<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenanceRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'staff', 'tenant']);
    }

    public function view(User $user, MaintenanceRequest $maintenanceRequest)
    {
        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }
        
        // Staff can view if assigned to them or unassigned
        if ($user->role === 'staff') {
            return $maintenanceRequest->assigned_to === $user->id || $maintenanceRequest->assigned_to === null;
        }
        
        // Tenant can only view their own requests
        return $user->id === $maintenanceRequest->tenant_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'staff', 'tenant']);
    }

    public function update(User $user, MaintenanceRequest $maintenanceRequest)
    {
        // Admin can update all
        if ($user->role === 'admin') {
            return true;
        }
        
        // Staff can update if assigned to them
        if ($user->role === 'staff') {
            return $maintenanceRequest->assigned_to === $user->id;
        }
        
        // Tenant can only update their own pending requests
        if ($user->role === 'tenant') {
            return $user->id === $maintenanceRequest->tenant_id && $maintenanceRequest->status === 'pending';
        }
        
        return false;
    }

    public function delete(User $user, MaintenanceRequest $maintenanceRequest)
    {
        // Delete action is disabled - use resolve instead
        return false;
    }

    public function restore(User $user, MaintenanceRequest $maintenanceRequest)
    {
        return false;
    }

    public function forceDelete(User $user, MaintenanceRequest $maintenanceRequest)
    {
        return false;
    }
    
    public function assign(User $user, MaintenanceRequest $maintenanceRequest)
    {
        return in_array($user->role, ['admin', 'staff']);
    }
    
    public function complete(User $user, MaintenanceRequest $maintenanceRequest)
    {
        if ($user->role === 'admin') {
            return true;
        }
        
        return $user->role === 'staff' && $maintenanceRequest->assigned_to === $user->id;
    }
}
