<?php

namespace App\Observers;

use App\Models\UtilityReading;
use App\Models\Tenant;
use App\Notifications\NewUtilityReadingNotification;

class UtilityReadingObserver
{
    /**
     * Handle the UtilityReading "created" event.
     */
    public function created(UtilityReading $utilityReading): void
    {
        // Load the utilityType relationship to ensure it's available in notifications
        $utilityReading->load('utilityType');
        
        // Notify the tenant about the new utility reading
        if ($utilityReading->tenant_id) {
            $tenant = Tenant::with('user')->find($utilityReading->tenant_id);
            
            if ($tenant && $tenant->user) {
                // Only send notifications to users with 'tenant' role
                // This prevents admins/staff from receiving tenant notifications
                if ($tenant->user->role === 'tenant') {
                    $tenant->user->notify(new NewUtilityReadingNotification($utilityReading));
                }
            }
        }
    }
}