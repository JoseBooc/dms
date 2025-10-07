<?php

namespace App\Filament\Resources\TenantBillResource\Pages;

use App\Filament\Resources\TenantBillResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantBills extends ListRecords
{
    protected static string $resource = TenantBillResource::class;

    protected function getActions(): array
    {
        return [
            // Tenants cannot create bills
        ];
    }
}
