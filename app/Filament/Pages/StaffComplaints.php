<?php

namespace App\Filament\Pages;

use App\Models\Complaint;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class StaffComplaints extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-circle';
    protected static string $view = 'filament.pages.staff-complaints';
    protected static ?string $title = 'My Complaints';
    protected static ?string $navigationLabel = 'Complaints';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 2;

    public $selectedComplaint = null;
    public $showModal = false;
    public $showNotesModal = false;
    public $showResolveModal = false;
    public $staffNotes = '';
    public $actionsTaken = '';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'staff';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'staff';
    }

    public function getComplaintsProperty()
    {
        return Complaint::where('assigned_to', Auth::id())
            ->with(['tenant', 'room'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOpenCountProperty()
    {
        return $this->complaints->where('status', 'pending')->count();
    }

    public function getInProgressCountProperty()
    {
        return $this->complaints->where('status', 'investigating')->count();
    }

    public function getResolvedCountProperty()
    {
        return $this->complaints->where('status', 'resolved')->count();
    }

    public function updateStatus($complaintId, $status)
    {
        $complaint = Complaint::findOrFail($complaintId);
        
        if ($complaint->assigned_to !== Auth::id()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You can only update complaints assigned to you.')
                ->danger()
                ->send();
            return;
        }

        // If trying to resolve, open resolve modal instead
        if ($status === 'resolved') {
            $this->openResolveModal($complaintId);
            return;
        }

        $complaint->update(['status' => $status]);
        
        $statusLabel = ucfirst(str_replace('_', ' ', $status));
        
        Notification::make()
            ->title('Status Updated')
            ->body("Complaint #{$complaintId} status changed to {$statusLabel}")
            ->success()
            ->send();
    }

    public function openDetailsModal($complaintId)
    {
        $this->selectedComplaint = Complaint::with(['tenant', 'room'])->findOrFail($complaintId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedComplaint = null;
    }

    public function openNotesModal($complaintId)
    {
        $complaint = Complaint::findOrFail($complaintId);
        
        if ($complaint->assigned_to !== Auth::id()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You can only edit complaints assigned to you.')
                ->danger()
                ->send();
            return;
        }

        if ($complaint->status === 'resolved' || $complaint->status === 'completed') {
            Notification::make()
                ->title('Cannot Edit')
                ->body('You cannot edit notes for resolved or completed complaints.')
                ->warning()
                ->send();
            return;
        }

        $this->selectedComplaint = $complaint;
        $this->staffNotes = $complaint->staff_notes ?? '';
        $this->showNotesModal = true;
    }

    public function closeNotesModal()
    {
        $this->showNotesModal = false;
        $this->selectedComplaint = null;
        $this->staffNotes = '';
    }

    public function saveNotes()
    {
        if (!$this->selectedComplaint) {
            return;
        }

        $this->selectedComplaint->update([
            'staff_notes' => $this->staffNotes
        ]);

        Notification::make()
            ->title('Notes Updated')
            ->body('Investigation notes have been saved.')
            ->success()
            ->send();

        $this->closeNotesModal();
    }

    public function openResolveModal($complaintId)
    {
        $complaint = Complaint::findOrFail($complaintId);
        
        if ($complaint->assigned_to !== Auth::id()) {
            Notification::make()
                ->title('Access Denied')
                ->body('You can only resolve complaints assigned to you.')
                ->danger()
                ->send();
            return;
        }

        $this->selectedComplaint = $complaint;
        $this->actionsTaken = $complaint->actions_taken ?? '';
        $this->showResolveModal = true;
    }

    public function closeResolveModal()
    {
        $this->showResolveModal = false;
        $this->selectedComplaint = null;
        $this->actionsTaken = '';
    }

    public function resolveComplaint()
    {
        if (!$this->selectedComplaint || empty($this->actionsTaken)) {
            Notification::make()
                ->title('Actions Required')
                ->body('You must specify the actions taken to resolve this complaint.')
                ->warning()
                ->send();
            return;
        }

        $this->selectedComplaint->update([
            'actions_taken' => $this->actionsTaken,
            'status' => 'resolved',
            'resolved_at' => now()
        ]);

        Notification::make()
            ->title('Complaint Resolved')
            ->body("Complaint #{$this->selectedComplaint->id} has been resolved.")
            ->success()
            ->send();

        $this->closeResolveModal();
    }

    protected function getActions(): array
    {
        return [
            Action::make('mark_all_in_progress')
                ->label('Mark All Open as In Progress')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => $this->openCount > 0)
                ->requiresConfirmation()
                ->action(function () {
                    Complaint::where('assigned_to', Auth::id())
                        ->where('status', 'open')
                        ->update(['status' => 'in_progress']);
                    
                    Notification::make()
                        ->title('Bulk Status Update')
                        ->body('All open complaints marked as in progress')
                        ->success()
                        ->send();
                }),
        ];
    }
}
