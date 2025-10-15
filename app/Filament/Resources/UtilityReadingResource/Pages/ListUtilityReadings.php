<?php

namespace App\Filament\Resources\UtilityReadingResource\Pages;

use App\Filament\Resources\UtilityReadingResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUtilityReadings extends ListRecords
{
    protected static string $resource = UtilityReadingResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        // Show only one record per tenant per date (water records preferred)
        // This avoids showing duplicate rows since we display both utilities in columns
        return parent::getTableQuery()
            ->whereHas('utilityType', function ($query) {
                $query->where('name', 'Water');
            })
            ->orderBy('reading_date', 'desc');
    }
}
