<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getActions(): array
    {
        return [
            // No delete action - data preservation policy
        ];
    }

    protected function getCancelledRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
