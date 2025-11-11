<?php

namespace App\Policies;

use App\Models\RoomAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoomAssignmentPolicy
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

    public function view(User $user, RoomAssignment $roomAssignment)
    {
        if (in_array($user->role, ['admin', 'staff'])) {
            return true;
        }
        
        $tenant = $user->tenant;
        return $tenant && $roomAssignment->tenant_id === $tenant->id;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, RoomAssignment $roomAssignment)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, RoomAssignment $roomAssignment)
    {
        return false;
    }

    public function restore(User $user, RoomAssignment $roomAssignment)
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, RoomAssignment $roomAssignment)
    {
        return false;
    }
}
