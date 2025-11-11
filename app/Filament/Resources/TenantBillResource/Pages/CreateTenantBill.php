<?php

namespace App\Filament\Resources\TenantBillResource\Pages;

use App\Filament\Resources\TenantBillResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTenantBill extends CreateRecord
{
    protected static string $resource = TenantBillResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
