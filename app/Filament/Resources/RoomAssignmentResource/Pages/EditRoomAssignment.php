<?php

namespace App\Filament\Resources\RoomAssignmentResource\Pages;

use App\Filament\Resources\RoomAssignmentResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomAssignment extends EditRecord
{
    protected static string $resource = RoomAssignmentResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
