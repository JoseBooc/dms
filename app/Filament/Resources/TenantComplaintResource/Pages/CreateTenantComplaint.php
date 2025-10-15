<?php

namespace App\Filament\Resources\TenantComplaintResource\Pages;

use App\Filament\Resources\TenantComplaintResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTenantComplaint extends CreateRecord
{
    protected static string $resource = TenantComplaintResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        
        if ($user && $user->role === 'tenant') {
            $data['tenant_id'] = $user->id;
            
            // Get tenant's room assignment
            $tenant = $user->tenant;
            if ($tenant) {
                $roomAssignment = $tenant->roomAssignments()->where('status', 'active')->first();
                if ($roomAssignment) {
                    $data['room_id'] = $roomAssignment->room_id;
                }
            }
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}