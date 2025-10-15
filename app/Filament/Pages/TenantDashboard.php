<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomAssignment;
use App\Models\Bill;
use App\Models\MaintenanceRequest;

class TenantDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Home';
    
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.tenant-dashboard';
    
    protected static ?string $title = 'Home';
    
    protected static ?string $slug = 'tenant-dashboard';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    public function getViewData(): array
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return [
                'currentAssignment' => null,
                'recentBills' => collect(),
                'maintenanceRequests' => collect(),
                'stats' => []
            ];
        }

        // Get current room assignment
        $currentAssignment = RoomAssignment::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('room')
            ->first();

        // Get recent bills
        $recentBills = Bill::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get pending maintenance requests
        $maintenanceRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Calculate stats
        $stats = [
            'total_bills' => Bill::where('tenant_id', $tenant->id)->count(),
            'unpaid_bills' => Bill::where('tenant_id', $tenant->id)->where('status', '!=', 'paid')->count(),
            'pending_maintenance' => $maintenanceRequests->count(),
        ];

        return [
            'currentAssignment' => $currentAssignment,
            'recentBills' => $recentBills,
            'maintenanceRequests' => $maintenanceRequests,
            'stats' => $stats
        ];
    }
}
