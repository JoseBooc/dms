<?php

namespace App\Filament\Resources\TestBillResource\Pages;

use App\Filament\Resources\TestBillResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestBills extends ListRecords
{
    protected static string $resource = TestBillResource::class;

    protected ?string $heading = 'Billing';

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Bill'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Bill'),
        ];
    }
}