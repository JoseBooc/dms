<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComplaintPolicy
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

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Complaint $complaint)
    {
        // Admin: can view all
        if ($user->role === 'admin') {
            return true;
        }
        
        // Staff: can view assigned complaints or all if not restricted
        if ($user->role === 'staff') {
            return $complaint->assigned_to === $user->id || $complaint->assigned_to === null;
        }
        
        // Tenant: can only view own complaints
        return $user->id === $complaint->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        // Admin and tenants can create complaints
        return in_array($user->role, ['admin', 'tenant']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Complaint $complaint)
    {
        // Admin: full access
        if ($user->role === 'admin') {
            return true;
        }
        
        // Staff: can update if assigned to them
        if ($user->role === 'staff') {
            return $complaint->assigned_to === $user->id;
        }
        
        // Tenant: can update own complaints only if still pending
        if ($user->role === 'tenant') {
            return $user->id === $complaint->tenant_id && $complaint->status === 'pending';
        }
        
        return false;
    }

    public function delete(User $user, Complaint $complaint)
    {
        // Delete action is disabled - use resolve instead
        return false;
    }

    public function restore(User $user, Complaint $complaint)
    {
        return false;
    }

    public function forceDelete(User $user, Complaint $complaint)
    {
        return false;
    }
    
    public function resolve(User $user, Complaint $complaint)
    {
        return in_array($user->role, ['admin', 'staff']);
    }
}
