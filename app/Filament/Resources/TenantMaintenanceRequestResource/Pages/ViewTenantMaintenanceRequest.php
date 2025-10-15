<?php

namespace App\Filament\Resources\TenantMaintenanceRequestResource\Pages;

use App\Filament\Resources\TenantMaintenanceRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTenantMaintenanceRequest extends ViewRecord
{
    protected static string $resource = TenantMaintenanceRequestResource::class;

    protected function getTitle(): string
    {
        return 'View Maintenance Request';
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status === 'pending'),
        ];
    }
}