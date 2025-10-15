<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;

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