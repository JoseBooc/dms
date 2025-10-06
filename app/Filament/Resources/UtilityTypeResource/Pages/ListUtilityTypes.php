<?php

namespace App\Filament\Resources\UtilityTypeResource\Pages;

use App\Filament\Resources\UtilityTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUtilityTypes extends ListRecords
{
    protected static string $resource = UtilityTypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
