<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UtilityReading;
use Illuminate\Auth\Access\HandlesAuthorization;

class UtilityReadingPolicy
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
        return in_array($user->role, ['admin', 'staff']);
    }

    public function view(User $user, UtilityReading $utilityReading)
    {
        return in_array($user->role, ['admin', 'staff']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'staff']);
    }

    public function update(User $user, UtilityReading $utilityReading)
    {
        if ($utilityReading->status === 'billed') {
            return false;
        }
        
        return in_array($user->role, ['admin', 'staff']);
    }

    public function delete(User $user, UtilityReading $utilityReading)
    {
        if ($utilityReading->status === 'billed') {
            return false;
        }
        
        return $user->role === 'admin';
    }

    public function restore(User $user, UtilityReading $utilityReading)
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, UtilityReading $utilityReading)
    {
        return false;
    }
    
    public function postReading(User $user)
    {
        return in_array($user->role, ['admin', 'staff']);
    }
    
    public function verify(User $user, UtilityReading $utilityReading)
    {
        return in_array($user->role, ['admin', 'staff']);
    }
}
