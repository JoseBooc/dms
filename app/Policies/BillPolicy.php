<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BillPolicy
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
        // Admin and staff can view all bills, tenant can view own bills
        return $user->role === 'admin' || $user->role === 'staff' || $user->role === 'tenant';
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Bill $bill)
    {
        // Admin and staff can view all, tenant can only view own bills
        if ($user->role === 'admin' || $user->role === 'staff') {
            return true;
        }
        
        return $user->id === $bill->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Only admin can create bills
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Bill $bill)
    {
        // Only admin can update bills
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Bill $bill)
    {
        // Only admin can delete bills (soft delete)
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Bill $bill)
    {
        // Only admin can restore bills
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Bill $bill)
    {
        // Only admin can force delete bills
        return $user->role === 'admin';
    }
    
    /**
     * Determine whether the user can waive penalties.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function waivePenalty(User $user, Bill $bill)
    {
        // Only admin can waive penalties
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create a penalty bill.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Bill  $bill
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function createPenaltyBill(User $user, Bill $bill)
    {
        // Cannot create penalty bill for another penalty bill
        if ($bill->bill_type === 'penalty') {
            return false;
        }

        // Admin can always create penalty bills
        if ($user->role === 'admin') {
            return true;
        }

        // Staff can create penalty bills if they can update bills
        if ($user->role === 'staff' && $this->update($user, $bill)) {
            return true;
        }

        // Tenants cannot create penalty bills
        return false;
    }
}
