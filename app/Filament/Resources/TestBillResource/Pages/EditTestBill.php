<?php

namespace App\Filament\Resources\TestBillResource\Pages;

use App\Filament\Resources\TestBillResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTestBill extends EditRecord
{
    protected static string $resource = TestBillResource::class;

    protected ?string $heading = 'Edit Bill';

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}