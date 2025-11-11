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
            Actions\Action::make('mark_resolved')
                ->label('Mark as Resolved')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Mark Complaint as Resolved')
                ->modalSubheading('This will mark the complaint as resolved and set the resolution date.')
                ->visible(fn () => !in_array($this->record->status, ['resolved', 'closed']))
                ->action(function () {
                    $this->record->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                    ]);
                    
                    $this->notify('success', 'Complaint marked as resolved.');
                    
                    // Refresh the page to show updated status
                    redirect()->to(ComplaintResource::getUrl('view', ['record' => $this->record]));
                }),
            Actions\EditAction::make(),
        ];
    }
}
