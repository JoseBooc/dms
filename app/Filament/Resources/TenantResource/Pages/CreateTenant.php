<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use App\Models\User;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create the user first
        $user = User::create([
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'name' => trim($data['first_name'] . ' ' . ($data['middle_name'] ? $data['middle_name'] . ' ' : '') . $data['last_name']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'tenant',
            'status' => 'active',
            'gender' => 'female',
        ]);

        // Set the user_id for the tenant
        $data['user_id'] = $user->id;
        $data['personal_email'] = $data['email'];
        
        // Set default values for removed fields (all-girls dormitory)
        $data['gender'] = 'female';
        $data['nationality'] = 'Filipino';
        $data['civil_status'] = 'single';
        
        // Set default values for removed identification fields
        $data['id_type'] = 'student_id';
        $data['id_number'] = 'N/A';
        $data['remarks'] = null;

        // Remove user-specific fields from tenant data
        unset($data['email'], $data['password']);
        
        // Log data for debugging
        \Log::info('Tenant data before create:', $data);

        return $data;
    }

    protected function afterCreate(): void
    {
        \Log::info('Tenant created successfully:', [
            'tenant_id' => $this->record->id,
            'user_id' => $this->record->user_id,
        ]);
    }

    protected function getCancelledRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
