<?php

namespace App\Filament\Resources\TenantMaintenanceRequestResource\Pages;

use App\Filament\Resources\TenantMaintenanceRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantMaintenanceRequest extends EditRecord
{
    protected static string $resource = TenantMaintenanceRequestResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'pending'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Maintenance request updated successfully!';
    }
}
