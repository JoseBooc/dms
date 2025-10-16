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

    public $stats = [];
    public $currentAssignment;
    public $recentBills;
    public $maintenanceRequests;
    public $maintenanceStats = [];

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    public function mount(): void
    {
        $data = $this->getViewData();
        $this->stats = $data['stats'];
        $this->currentAssignment = $data['currentAssignment'];
        $this->recentBills = $data['recentBills'];
        $this->maintenanceRequests = $data['maintenanceRequests'];
        $this->maintenanceStats = $data['maintenanceStats'];
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

        // Get recent bills (bills reference user_id, not tenant_id)
        $recentBills = Bill::where('tenant_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get pending maintenance requests
        $maintenanceRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Calculate stats (bills use user_id, maintenance uses tenant_id)
        $stats = [
            'total_bills' => Bill::where('tenant_id', $user->id)->count(),
            'unpaid_bills' => Bill::where('tenant_id', $user->id)->where('status', '!=', 'paid')->count(),
            'pending_maintenance' => $maintenanceRequests->count(),
        ];

        // Calculate maintenance stats for overview cards
        $maintenanceStats = [
            'total_requests' => MaintenanceRequest::where('tenant_id', $tenant->id)->count(),
            'pending_requests' => MaintenanceRequest::where('tenant_id', $tenant->id)->where('status', 'pending')->count(),
            'in_progress_requests' => MaintenanceRequest::where('tenant_id', $tenant->id)->where('status', 'in_progress')->count(),
            'completed_requests' => MaintenanceRequest::where('tenant_id', $tenant->id)->where('status', 'completed')->count(),
        ];

        return [
            'currentAssignment' => $currentAssignment,
            'recentBills' => $recentBills,
            'maintenanceRequests' => $maintenanceRequests,
            'stats' => $stats,
            'maintenanceStats' => $maintenanceStats
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Moved to main content area
        ];
    }
}
