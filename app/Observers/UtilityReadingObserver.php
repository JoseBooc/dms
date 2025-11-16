<?php

namespace App\Observers;

use App\Models\UtilityReading;
use App\Models\User;
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
            $tenant = User::find($utilityReading->tenant_id);
            if ($tenant) {
                $tenant->notify(new NewUtilityReadingNotification($utilityReading));
            }
        }
    }
}