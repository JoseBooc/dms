<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use App\Models\User;
use App\Notifications\MaintenanceRequestNotification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMaintenanceRequest extends ViewRecord
{
    protected static string $resource = MaintenanceRequestResource::class;
    
    protected static string $view = 'filament.resources.maintenance-request-resource.pages.view-maintenance-request';
    
    protected function getActions(): array
    {
        return [
            Actions\Action::make('mark_completed')
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
                    
                    // Refresh the page to show updated status
                    redirect()->to(MaintenanceRequestResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions\EditAction::make(),
        ];
    }
}
