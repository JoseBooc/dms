<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Livewire\Livewire;
use App\Http\Livewire\NotificationDropdown;
use App\Models\Bill;
use App\Models\MaintenanceRequest;
use App\Models\Complaint;
use App\Models\UtilityReading;
use App\Observers\BillObserver;
use App\Observers\MaintenanceRequestObserver;
use App\Observers\ComplaintObserver;
use App\Observers\UtilityReadingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register observers
        Bill::observe(BillObserver::class);
        MaintenanceRequest::observe(MaintenanceRequestObserver::class);
        Complaint::observe(ComplaintObserver::class);
        UtilityReading::observe(UtilityReadingObserver::class);

        // Register the Livewire component
        Livewire::component('notification-dropdown', NotificationDropdown::class);

        // Add notification dropdown to Filament navigation
        Filament::serving(function () {
            Filament::registerRenderHook(
                'global-search.end',
                fn (): string => '<div class="ml-4">' . \Livewire\Livewire::mount('notification-dropdown')->html() . '</div>'
            );
            
            // Register user menu items
            Filament::registerUserMenuItems([
                'change-password' => UserMenuItem::make()
                    ->label('Change Password')
                    ->url('/dashboard/change-password')
                    ->icon('heroicon-s-key'),
            ]);
        });
    }
}
