<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\UtilityReading;

class NewUtilityReadingNotification extends Notification
{
    use Queueable;

    protected $utilityReading;

    public function __construct(UtilityReading $utilityReading)
    {
        $this->utilityReading = $utilityReading;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $utilityTypeName = $this->utilityReading->utilityType ? $this->utilityReading->utilityType->name : 'Unknown Utility';
        
        return [
            'type' => 'new_utility_reading',
            'title' => 'New Utility Reading',
            'message' => 'New ' . $utilityTypeName . ' reading recorded for your room',
            'reading_id' => $this->utilityReading->id,
            'utility_type' => $utilityTypeName,
            'current_reading' => $this->utilityReading->current_reading,
            'consumption' => $this->utilityReading->consumption,
            'action_url' => url('/dashboard'),
            'icon' => 'heroicon-o-lightning-bolt',
            'color' => 'info'
        ];
    }
}