<?php

namespace App\Filament\Resources\UtilityReadingResource\Pages;

use App\Filament\Resources\UtilityReadingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUtilityReading extends CreateRecord
{
    protected static string $resource = UtilityReadingResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set recorded_by to current user
        $data['recorded_by'] = auth()->id();
        
        // Set status to pending if not set
        $data['status'] = $data['status'] ?? 'pending';
        
        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Utility reading created successfully';
    }
}
