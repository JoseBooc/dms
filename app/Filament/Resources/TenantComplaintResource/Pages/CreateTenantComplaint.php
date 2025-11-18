<?php

namespace App\Filament\Resources\TenantComplaintResource\Pages;

use App\Filament\Resources\TenantComplaintResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTenantComplaint extends CreateRecord
{
    protected static string $resource = TenantComplaintResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'tenant') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'general' => ['Invalid user access.'],
            ]);
        }
        
        $data['tenant_id'] = $user->id;
        
        // Get tenant's room assignment - must be active
        $tenant = $user->tenant;
        if (!$tenant) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'general' => ['No tenant profile found.'],
            ]);
        }
        
        $roomAssignment = $tenant->roomAssignments()->whereIn('status', ['active', 'pending', 'inactive'])->first();
        if (!$roomAssignment) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'general' => ['You cannot submit a complaint because you do not have a room assignment. Please contact the administration if you believe this is an error.'],
            ]);
        }
        
        $data['room_id'] = $roomAssignment->room_id;
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}