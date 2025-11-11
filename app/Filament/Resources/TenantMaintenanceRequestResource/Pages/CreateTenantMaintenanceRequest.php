<?php

namespace App\Filament\Resources\TenantMaintenanceRequestResource\Pages;

use App\Filament\Resources\TenantMaintenanceRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantMaintenanceRequest extends CreateRecord
{
    protected static string $resource = TenantMaintenanceRequestResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
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
        }
        
        $data['status'] = 'pending';
        
        return $data;
    }
}
