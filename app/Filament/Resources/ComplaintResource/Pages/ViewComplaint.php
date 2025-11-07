<?php

namespace App\Filament\Resources\ComplaintResource\Pages;

use App\Filament\Resources\ComplaintResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewComplaint extends ViewRecord
{
    protected static string $resource = ComplaintResource::class;
    
    protected static string $view = 'filament.resources.complaint-resource.pages.view-complaint';
    
    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
