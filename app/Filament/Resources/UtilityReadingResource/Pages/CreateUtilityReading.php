<?php

namespace App\Filament\Resources\UtilityReadingResource\Pages;

use App\Filament\Resources\UtilityReadingResource;
use App\Models\UtilityReading;
use App\Models\UtilityType;
use App\Models\Tenant;
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
            throw new \Exception('Water or Electricity utility types not found. Please run the UtilityTypeSeeder.');
        }

        // Get the tenant's current room assignment
        $tenant = Tenant::find($data['tenant_id']);
        $roomAssignment = $tenant->roomAssignments()->where('status', 'active')->first();
        
        if (!$roomAssignment) {
            throw new \Exception('No active room assignment found for this tenant.');
        }

        // Check for existing readings for the same room and date
        $existingWaterReading = UtilityReading::where('room_id', $roomAssignment->room_id)
            ->where('utility_type_id', $waterType->id)
            ->where('reading_date', $data['water_reading_date'])
            ->first();

        $existingElectricityReading = UtilityReading::where('room_id', $roomAssignment->room_id)
            ->where('utility_type_id', $electricityType->id)
            ->where('reading_date', $data['electricity_reading_date'])
            ->first();

        if ($existingWaterReading) {
            throw new \Exception('A water reading already exists for room ' . $roomAssignment->room->room_number . ' on ' . \Carbon\Carbon::parse($data['water_reading_date'])->format('M j, Y') . '. Please use a different date or edit the existing reading.');
        }

        if ($existingElectricityReading) {
            throw new \Exception('An electricity reading already exists for room ' . $roomAssignment->room->room_number . ' on ' . \Carbon\Carbon::parse($data['electricity_reading_date'])->format('M j, Y') . '. Please use a different date or edit the existing reading.');
        }

        // Create water reading
        $waterReading = UtilityReading::create([
            'room_id' => $roomAssignment->room_id,
            'tenant_id' => $data['tenant_id'],
            'utility_type_id' => $waterType->id,
            'current_reading' => $data['water_current_reading'],
            'price' => $data['water_price'],
            'reading_date' => $data['water_reading_date'],
            'notes' => $data['water_notes'] ?? null,
            'recorded_by' => auth()->id(),
        ]);

        // Create electricity reading
        $electricityReading = UtilityReading::create([
            'room_id' => $roomAssignment->room_id,
            'tenant_id' => $data['tenant_id'],
            'utility_type_id' => $electricityType->id,
            'current_reading' => $data['electricity_current_reading'],
            'price' => $data['electricity_price'],
            'reading_date' => $data['electricity_reading_date'],
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
            ->title('Both utility readings saved')
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
