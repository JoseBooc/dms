<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Livewire\Livewire;
use App\Http\Livewire\NotificationDropdown;
use App\Models\Bill;
use App\Observers\BillObserver;

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

        // Register the Livewire component
        Livewire::component('notification-dropdown', NotificationDropdown::class);

        // Add notification dropdown to Filament navigation
        Filament::serving(function () {
            Filament::registerRenderHook(
                'global-search.end',
                fn (): string => '<div class="ml-4">' . \Livewire\Livewire::mount('notification-dropdown')->html() . '</div>'
            );
        });
    }
}
