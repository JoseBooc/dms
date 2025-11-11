<?php

namespace App\Policies;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepositPolicy
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

    public function view(User $user, Deposit $deposit)
    {
        if (in_array($user->role, ['admin', 'staff'])) {
            return true;
        }
        
        return $user->id === $deposit->tenant_id;
    }

    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Deposit $deposit)
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Deposit $deposit)
    {
        return false;
    }

    public function restore(User $user, Deposit $deposit)
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Deposit $deposit)
    {
        return false;
    }
    
    public function refund(User $user, Deposit $deposit)
    {
        return $user->role === 'admin';
    }
    
    public function archiveDeduction(User $user, Deposit $deposit)
    {
        return $user->role === 'admin';
    }
    
    public function restoreDeduction(User $user, Deposit $deposit)
    {
        return $user->role === 'admin';
    }
}
