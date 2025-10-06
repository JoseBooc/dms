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

        // Remove user-specific fields from tenant data
        unset($data['email'], $data['password']);

        return $data;
    }

    protected function getCancelledRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
