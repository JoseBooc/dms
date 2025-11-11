<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Models\MaintenanceRequest;

class StaffMaintenanceWidget extends Widget
{
    protected static string $view = 'filament.widgets.staff-maintenance-widget';
    
    protected int | string | array $columnSpan = 'full';

    public function getMaintenanceRequests()
    {
        return MaintenanceRequest::where('assigned_to', Auth::id())
            ->with(['tenant', 'room'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getStats()
    {
        $requests = MaintenanceRequest::where('assigned_to', Auth::id());
        
        return [
            'total' => $requests->count(),
            'pending' => $requests->clone()->where('status', 'pending')->count(),
            'in_progress' => $requests->clone()->where('status', 'in_progress')->count(),
            'completed' => $requests->clone()->where('status', 'completed')->count(),
        ];
    }

    public static function canView(): bool
    {
        return Auth::user()?->role === 'staff';
    }
}
