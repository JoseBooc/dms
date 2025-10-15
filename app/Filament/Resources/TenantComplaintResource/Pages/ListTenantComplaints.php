<?php

namespace App\Filament\Resources\TenantComplaintResource\Pages;

use App\Filament\Resources\TenantComplaintResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantComplaints extends ListRecords
{
    protected static string $resource = TenantComplaintResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Submit New Complaint'),
        ];
    }
}