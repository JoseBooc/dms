<?php

namespace App\Filament\Pages;

use App\Models\Complaint;
use Filament\Forms\Components\Textarea;
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

        $updateData = ['status' => $status];
        if ($status === 'resolved') {
            $updateData['resolved_at'] = now();
        } elseif ($status === 'completed') {
            $updateData['resolved_at'] = now();
        }

        $complaint->update($updateData);
        
        if ($status === 'resolved') {
            Notification::make()
                ->title('Complaint Marked as Resolved')
                ->body("Complaint #{$complaintId} has been marked as resolved")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Status Updated')
                ->body("Complaint #{$complaintId} status updated to {$status}")
                ->success()
                ->send();
        }
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

    public function addResolution($complaintId, $resolution)
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

        $complaint->update([
            'resolution' => $resolution,
            'status' => 'resolved',
            'resolved_at' => now()
        ]);
        
        Notification::make()
            ->title('Complaint Resolved')
            ->body("Complaint #{$complaintId} has been resolved with resolution notes")
            ->success()
            ->send();
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
