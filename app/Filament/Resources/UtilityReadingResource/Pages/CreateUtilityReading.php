<?php

namespace App\Filament\Resources\UtilityReadingResource\Pages;

use App\Filament\Resources\UtilityReadingResource;
use App\Models\UtilityReading;
use App\Models\UtilityType;
use App\Models\Tenant;
use App\Models\Room;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CreateUtilityReading extends CreateRecord
{
    protected static string $resource = UtilityReadingResource::class;

    // Disable create another functionality
    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        // Get the utility type IDs
        $waterType = UtilityType::where('name', 'Water')->first();
        $electricityType = UtilityType::where('name', 'Electricity')->first();

        // Check if utility types exist
        if (!$waterType || !$electricityType) {
            Notification::make()
                ->title('System Configuration Error')
                ->body('Water or Electricity utility types not found. Please contact the administrator to run the UtilityTypeSeeder.')
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }

        // Get the selected room
        $room = Room::find($data['room_id']);
        
        if (!$room) {
            Notification::make()
                ->title('Room Not Found')
                ->body('The selected room could not be found. Please select a valid room.')
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }

        // Check if room has active tenants
        $activeAssignments = $room->assignments()->where('status', 'active')->get();
        
        if ($activeAssignments->isEmpty()) {
            Notification::make()
                ->title('No Active Tenants')
                ->body('Room ' . $room->room_number . ' has no active tenants. Please assign tenants to this room before creating utility readings.')
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }

        // Check for existing readings for the same room and date
        $existingWaterReading = UtilityReading::where('room_id', $room->id)
            ->where('utility_type_id', $waterType->id)
            ->where('reading_date', $data['reading_date'])
            ->first();

        $existingElectricityReading = UtilityReading::where('room_id', $room->id)
            ->where('utility_type_id', $electricityType->id)
            ->where('reading_date', $data['reading_date'])
            ->first();

        if ($existingWaterReading) {
            Notification::make()
                ->title('Duplicate Utility Reading')
                ->body('A reading already exists for room ' . $room->room_number . ' on ' . Carbon::parse($data['reading_date'])->format('M j, Y') . '. Please use a different date or edit the existing reading.')
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }

        if ($existingElectricityReading) {
            Notification::make()
                ->title('Duplicate Utility Reading')
                ->body('A reading already exists for room ' . $room->room_number . ' on ' . Carbon::parse($data['reading_date'])->format('M j, Y') . '. Please use a different date or edit the existing reading.')
                ->danger()
                ->persistent()
                ->send();
            
            $this->halt();
        }

        // Create water reading (use first active tenant for tenant_id field)
        $primaryTenant = $activeAssignments->first()->tenant_id;
        
        $waterReading = UtilityReading::create([
            'room_id' => $room->id,
            'tenant_id' => $primaryTenant,
            'utility_type_id' => $waterType->id,
            'current_reading' => $data['water_current_reading'],
            'price' => $data['water_price'],
            'reading_date' => $data['reading_date'],
            'notes' => $data['water_notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        // Create electricity reading
        $electricityReading = UtilityReading::create([
            'room_id' => $room->id,
            'tenant_id' => $primaryTenant,
            'utility_type_id' => $electricityType->id,
            'current_reading' => $data['electricity_current_reading'],
            'price' => $data['electricity_price'],
            'reading_date' => $data['reading_date'],
            'notes' => $data['electricity_notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        // Return the water reading as the "primary" record for redirect purposes
        return $waterReading;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Utility readings created successfully';
    }

    protected function afterCreate(): void
    {
        // Send a custom notification
        Notification::make()
            ->title('Utility reading saved')
            ->body('Water and electricity readings have been successfully recorded.')
            ->success()
            ->send();
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        $title = $this->getCreatedNotificationTitle();

        if (blank($title)) {
            return null;
        }

        return Notification::make()
            ->success()
            ->title($title);
    }

    public function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }


}
