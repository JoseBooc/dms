<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Notifications\MaintenanceRequestNotification;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateMaintenanceRequest extends CreateRecord
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            // disableFormOnSubmit() removed - not available in Filament v2
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check for duplicate maintenance request in the last 24 hours
        $duplicate = MaintenanceRequest::where('tenant_id', $data['tenant_id'])
            ->where('room_id', $data['room_id'])
            ->where('area', $data['area'])
            ->where('created_at', '>=', now()->subDay())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->first();

        if ($duplicate) {
            // Log the duplicate attempt
            if (class_exists('\App\Services\AuditLogService')) {
                app(\App\Services\AuditLogService::class)->log(
                    $duplicate,
                    'duplicate_maintenance_request_prevented',
                    null,
                    [
                        'attempted_by' => auth()->id(),
                        'duplicate_of' => $duplicate->id,
                        'area' => $data['area'],
                        'room_id' => $data['room_id'],
                    ],
                    'Duplicate maintenance request prevented for same area within 24 hours'
                );
            }

            // Show error notification
            Notification::make()
                ->title('Duplicate Request Detected')
                ->body('A maintenance request for this room and area has already been submitted today. Please check existing requests before creating a new one.')
                ->danger()
                ->persistent()
                ->send();

            // Throw validation exception to prevent creation
            throw ValidationException::withMessages([
                'area' => 'A maintenance request for this room and area has already been submitted today.',
            ]);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $maintenanceRequest = $this->record;
        
        // Notify all admins about new maintenance request
        $admins = User::where('role', 'admin')->orWhere('role', 'staff')->get();
        foreach ($admins as $admin) {
            $admin->notify(new MaintenanceRequestNotification($maintenanceRequest, 'new'));
        }
    }
}
