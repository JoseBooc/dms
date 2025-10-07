<?php

namespace App\Filament\Resources\TenantBillResource\Pages;

use App\Filament\Resources\TenantBillResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTenantBill extends ViewRecord
{
    protected static string $resource = TenantBillResource::class;

    protected function getActions(): array
    {
        return [
            // No actions needed for tenant bill view
        ];
    }
}