<?php

namespace App\Filament\Pages;

use App\Models\MaintenanceRequest;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\Modal\Actions\Action as ModalAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StaffMaintenanceRequests extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.staff-maintenance-requests';
    protected static ?string $title = 'My Maintenance Requests';
    protected static ?string $navigationLabel = 'Maintenance Requests';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;

    public $selectedRequest = null;
    public $showModal = false;
    public $showCompletionModal = false;
    public $completionNotes = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Textarea::make('completionNotes')
                ->label('Completion Notes')
                ->placeholder('Enter notes about the completed work...')
                ->rows(3)
                ->required()
                ->minLength(10)
                ->maxLength(1000)
                ->helperText('Please provide detailed notes about the work completed (minimum 10 characters).'),
        ];
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'staff';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'staff';
    }

    public function getMaintenanceRequestsProperty()
    {
        return MaintenanceRequest::where('assigned_to', Auth::id())
            ->with(['tenant', 'room'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingCountProperty()
    {
        return $this->maintenanceRequests->where('status', 'pending')->count();
    }

    public function getInProgressCountProperty()
    {
        return $this->maintenanceRequests->where('status', 'in_progress')->count();
    }

    public function getCompletedCountProperty()
    {
        return $this->maintenanceRequests->where('status', 'completed')->count();
    }

    public function updateStatus($requestId, $status)
    {
        $request = MaintenanceRequest::findOrFail($requestId);
        
        if ($request->assigned_to !== Auth::id()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You can only update requests assigned to you.')
                ->danger()
                ->send();
            return;
        }

        $request->update(['status' => $status]);
        
        Notification::make()
            ->title('Status Updated')
            ->body("Request #{$requestId} status updated to {$status}")
            ->success()
            ->send();
    }

    public function openDetailsModal($requestId)
    {
        $this->selectedRequest = MaintenanceRequest::with(['tenant', 'room'])->findOrFail($requestId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedRequest = null;
        $this->reset(['completionNotes']);
    }

    public function openCompletionModal($requestId)
    {
        $this->selectedRequest = MaintenanceRequest::with(['tenant', 'room'])->findOrFail($requestId);
        $this->showCompletionModal = true;
    }

    public function closeCompletionModal()
    {
        $this->showCompletionModal = false;
        $this->selectedRequest = null;
        $this->reset(['completionNotes']);
    }

    public function completeWork($requestId)
    {
        try {
            // Validate the form first - this will throw ValidationException if invalid
            $state = $this->form->getState();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw the validation exception to show errors
            throw $e;
        }
        
        $request = MaintenanceRequest::findOrFail($requestId);
        
        if ($request->assigned_to !== Auth::id()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You can only update requests assigned to you.')
                ->danger()
                ->send();
            return;
        }

        // Maintenance request completion now uses notes only

        $request->update([
            'status' => 'completed',
            'completion_notes' => $state['completionNotes'],
        ]);

        Notification::make()
            ->title('Request Marked as Completed')
            ->body("Request #{$request->id} has been marked as completed.")
            ->success()
            ->send();

        // Close the completion modal and reset form
        $this->closeCompletionModal();
    }

    protected function getActions(): array
    {
        return [
            Action::make('mark_all_in_progress')
                ->label('Mark All Pending as In Progress')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => $this->pendingCount > 0)
                ->requiresConfirmation()
                ->action(function () {
                    MaintenanceRequest::where('assigned_to', Auth::id())
                        ->where('status', 'pending')
                        ->update(['status' => 'in_progress']);
                    
                    Notification::make()
                        ->title('Bulk Status Update')
                        ->body('All pending requests marked as in progress')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getRequestActions($requestId): array
    {
        $request = MaintenanceRequest::find($requestId);
        if (!$request || $request->assigned_to !== Auth::id()) {
            return [];
        }

        $actions = [
            Action::make('view_details')
                ->label('View Details')
                ->icon('heroicon-o-eye')
                ->color('secondary')
                ->modalHeading("Maintenance Request #{$request->id}")
                ->modalContent(view('filament.components.maintenance-request-details', ['request' => $request]))
                ->modalActions([
                    ModalAction::make('close')
                        ->label('Close')
                        ->close(),
                ])
        ];

        if ($request->status === 'pending') {
            $actions[] = Action::make('start_work')
                ->label('Start Work')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () use ($request) {
                    $request->update(['status' => 'in_progress']);
                    Notification::make()
                        ->title('Status Updated')
                        ->body("Request #{$request->id} marked as in progress")
                        ->success()
                        ->send();
                });
        }

        if ($request->status === 'in_progress') {
            $actions[] = Action::make('complete_work')
                ->label('Mark as Completed')
                ->icon('heroicon-o-check')
                ->color('warning')
                ->modalHeading("Mark Maintenance Request #{$request->id} as Completed")
                ->form($this->getFormSchema())
                ->action(function (array $data) use ($request) {
                    $request->update([
                        'status' => 'completed',
                        'completion_notes' => $data['completionNotes'],
                    ]);

                    Notification::make()
                        ->title('Request Marked as Completed')
                        ->body("Request #{$request->id} has been marked as completed.")
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }
}
