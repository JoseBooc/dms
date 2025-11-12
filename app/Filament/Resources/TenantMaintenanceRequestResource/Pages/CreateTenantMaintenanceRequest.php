<?php

namespace App\Filament\Resources\TenantMaintenanceRequestResource\Pages;

use App\Filament\Resources\TenantMaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class CreateTenantMaintenanceRequest extends CreateRecord
{
    protected static string $resource = TenantMaintenanceRequestResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            // disableFormOnSubmit() removed - not available in Filament v2
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Maintenance request submitted successfully!';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $tenant = $user->tenant;
        
        if ($tenant) {
            $data['tenant_id'] = $tenant->id;
            
            // Get current room assignment
            $currentAssignment = \App\Models\RoomAssignment::where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->first();
                
            if ($currentAssignment) {
                $data['room_id'] = $currentAssignment->room_id;
            }

            // Check for duplicate maintenance request in the last 24 hours
            $duplicate = MaintenanceRequest::where('tenant_id', $tenant->id)
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
                        'Tenant attempted to create duplicate maintenance request within 24 hours'
                    );
                }

                // Show error notification
                Notification::make()
                    ->title('Duplicate Request Detected')
                    ->body('You have already submitted a maintenance request for this area today. Please wait for the existing request to be processed or contact the admin.')
                    ->danger()
                    ->persistent()
                    ->send();

                // Throw validation exception to prevent creation
                throw ValidationException::withMessages([
                    'area' => 'You have already submitted a maintenance request for this area today.',
                ]);
            }
        }
        
        $data['status'] = 'pending';
        
        return $data;
    }
}
