<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\Bill;
use App\Models\MaintenanceRequest;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-grid';
    
    protected static ?string $navigationLabel = 'Dashboard';
    
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.dashboard';
    
    protected static ?string $title = 'Dashboard';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'staff']);
    }

    public function getViewData(): array
    {
        // Get overview statistics for admin/staff
        $stats = [
            'total_rooms' => Room::count(),
            'occupied_rooms' => Room::where('status', 'occupied')->count(),
            'available_rooms' => Room::where('status', 'available')->count(),
            'total_tenants' => Tenant::count(),
            'unpaid_bills' => Bill::where('status', '!=', 'paid')->count(),
            'pending_maintenance' => MaintenanceRequest::where('status', '!=', 'completed')->count(),
            'monthly_revenue' => Bill::where('status', 'paid')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),
        ];

        // Calculate occupancy rate
        $stats['occupancy_rate'] = $stats['total_rooms'] > 0 
            ? round(($stats['occupied_rooms'] / $stats['total_rooms']) * 100, 1) 
            : 0;

        return ['stats' => $stats];
    }
}
