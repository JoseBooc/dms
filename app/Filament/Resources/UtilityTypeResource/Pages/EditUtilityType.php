<?php

namespace App\Filament\Resources\UtilityTypeResource\Pages;

use App\Filament\Resources\UtilityTypeResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUtilityType extends EditRecord
{
    protected static string $resource = UtilityTypeResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
