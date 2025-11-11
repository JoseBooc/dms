<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\Deposit;
use App\Models\RoomAssignment;
use App\Models\MaintenanceRequest;
use App\Models\Complaint;
use App\Models\UtilityReading;
use App\Policies\BillPolicy;
use App\Policies\DepositPolicy;
use App\Policies\RoomAssignmentPolicy;
use App\Policies\MaintenanceRequestPolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\UtilityReadingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Bill::class => BillPolicy::class,
        Deposit::class => DepositPolicy::class,
        RoomAssignment::class => RoomAssignmentPolicy::class,
        MaintenanceRequest::class => MaintenanceRequestPolicy::class,
        Complaint::class => ComplaintPolicy::class,
        UtilityReading::class => UtilityReadingPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return $user->role === 'admin' ? true : null;
        });
    }
}
