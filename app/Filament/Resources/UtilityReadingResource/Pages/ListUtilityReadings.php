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
        // Show all utility readings, ordered by most recent first
        // New unified readings have NULL utility_type_id and contain both water & electric data
        // Old readings may still have utility_type_id set (legacy data)
        return parent::getTableQuery()
            ->orderBy('reading_date', 'desc')
            ->orderBy('id', 'desc');
    }
}
