<?php

namespace App\Filament\Resources\TenantComplaintResource\Pages;

use App\Filament\Resources\TenantComplaintResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTenantComplaint extends EditRecord
{
    protected static string $resource = TenantComplaintResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->status === 'open'),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}