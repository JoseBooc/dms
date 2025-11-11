<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Notifications\MaintenanceRequestNotification;

class CreateMaintenanceRequest extends CreateRecord
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
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
