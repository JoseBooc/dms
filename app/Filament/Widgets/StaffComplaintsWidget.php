<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Models\Complaint;

class StaffComplaintsWidget extends Widget
{
    protected static string $view = 'filament.widgets.staff-complaints-widget';
    
    protected int | string | array $columnSpan = 'full';

    public function getComplaints()
    {
        return Complaint::where('assigned_to', Auth::id())
            ->with(['tenant', 'room'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getStats()
    {
        $complaints = Complaint::where('assigned_to', Auth::id());
        
        return [
            'total' => $complaints->count(),
            'pending' => $complaints->clone()->where('status', 'pending')->count(),
            'in_progress' => $complaints->clone()->where('status', 'in_progress')->count(),
            'resolved' => $complaints->clone()->where('status', 'resolved')->count(),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()?->role === 'staff';
    }
}
