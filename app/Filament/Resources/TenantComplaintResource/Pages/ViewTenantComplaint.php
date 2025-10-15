<?php

namespace App\Filament\Resources\TenantComplaintResource\Pages;

use App\Filament\Resources\TenantComplaintResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTenantComplaint extends ViewRecord
{
    protected static string $resource = TenantComplaintResource::class;

    protected function getTitle(): string
    {
        return 'View Complaint';
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => in_array($record->status, ['open', 'investigating'])),
        ];
    }
}