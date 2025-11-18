<?php

namespace App\Filament\Resources\TenantMaintenanceRequestResource\Pages;

use App\Filament\Resources\TenantMaintenanceRequestResource;
use App\Models\RoomAssignment;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTenantMaintenanceRequests extends ListRecords
{
    protected static string $resource = TenantMaintenanceRequestResource::class;

    protected function getActions(): array
    {
        $user = Auth::user();
        $tenant = $user?->tenant;
        
        // Check if tenant has an active room assignment
        $hasActiveAssignment = false;
        if ($tenant) {
            $hasActiveAssignment = RoomAssignment::where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->exists();
        }
        
        $actions = [];
        
        if ($hasActiveAssignment) {
            $actions[] = Actions\CreateAction::make()
                ->label('Submit New Request')
                ->icon('heroicon-o-plus');
        }
        
        return $actions;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // We can add status widgets here later
        ];
    }
}
