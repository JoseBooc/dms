<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use App\Notifications\MaintenanceRequestNotification;

class EditMaintenanceRequest extends EditRecord
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('resolve')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark Maintenance Request as Completed')
                ->modalSubheading('This will mark the maintenance request as completed and set the completion date.')
                ->visible(fn () => $this->record->status !== 'completed')
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    
                    // Notify the tenant who made the request
                    $tenant = User::find($this->record->tenant_id);
                    if ($tenant) {
                        $tenant->notify(new MaintenanceRequestNotification($this->record, 'completed'));
                    }
                    
                    $this->notify('success', 'Maintenance request marked as completed.');
                }),
        ];
    }

    protected function afterSave(): void
    {
        $maintenanceRequest = $this->record;
        $originalData = $this->record->getOriginal();
        
        // Check if status was updated
        if ($maintenanceRequest->status !== $originalData['status']) {
            // Notify the tenant who made the request
            $tenant = User::find($maintenanceRequest->tenant_id);
            if ($tenant) {
                $notificationType = $maintenanceRequest->status === 'completed' ? 'completed' : 'update';
                $tenant->notify(new MaintenanceRequestNotification($maintenanceRequest, $notificationType));
            }
            
            // Also notify admins if not completed
            if ($maintenanceRequest->status !== 'completed') {
                $admins = User::where('role', 'admin')->orWhere('role', 'staff')->get();
                foreach ($admins as $admin) {
                    $admin->notify(new MaintenanceRequestNotification($maintenanceRequest, 'update'));
                }
            }
        }
    }
}
