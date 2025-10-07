<?php

namespace App\Filament\Resources\TenantBillResource\Pages;

use App\Filament\Resources\TenantBillResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantBill extends EditRecord
{
    protected static string $resource = TenantBillResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
