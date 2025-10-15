<?php

namespace App\Filament\Resources\TenantMaintenanceRequestResource\Pages;

use App\Filament\Resources\TenantMaintenanceRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantMaintenanceRequests extends ListRecords
{
    protected static string $resource = TenantMaintenanceRequestResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Submit New Request')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // We can add status widgets here later
        ];
    }
}
