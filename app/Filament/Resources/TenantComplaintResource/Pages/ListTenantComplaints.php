<?php

namespace App\Filament\Resources\TenantComplaintResource\Pages;

use App\Filament\Resources\TenantComplaintResource;
use App\Models\RoomAssignment;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListTenantComplaints extends ListRecords
{
    protected static string $resource = TenantComplaintResource::class;

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
                ->label('Submit New Complaint');
        }
        
        return $actions;
    }
}