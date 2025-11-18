<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomAssignment;
use App\Models\Bill;
use App\Models\MaintenanceRequest;
use App\Models\UtilityReading;

class TenantDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-office-building';
    
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
    public $utilityReadings;

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
        $this->utilityReadings = $data['utilityReadings'];
    }

    public function getViewData(): array
    {
        $user = Auth::user();
        
        // Ensure user exists and is a tenant
        if (!$user || $user->role !== 'tenant') {
            return [
                'currentAssignment' => null,
                'recentBills' => collect(),
                'maintenanceRequests' => collect(),
                'stats' => [
                    'total_bills' => 0,
                    'unpaid_bills' => 0,
                    'pending_maintenance' => 0,
                ],
                'maintenanceStats' => [
                    'total_requests' => 0,
                    'pending_requests' => 0,
                    'in_progress_requests' => 0,
                    'completed_requests' => 0,
                ],
                'utilityReadings' => collect(),
            ];
        }
        
        $tenant = $user->tenant;
        
        // If tenant relationship doesn't exist, return empty data
        if (!$tenant) {
            return [
                'currentAssignment' => null,
                'recentBills' => collect(),
                'maintenanceRequests' => collect(),
                'stats' => [
                    'total_bills' => 0,
                    'unpaid_bills' => 0,
                    'pending_maintenance' => 0,
                ],
                'maintenanceStats' => [
                    'total_requests' => 0,
                    'pending_requests' => 0,
                    'in_progress_requests' => 0,
                    'completed_requests' => 0,
                ],
                'utilityReadings' => collect(),
            ];
        }

        // Get current room assignment (active, inactive, or pending)
        $currentAssignment = RoomAssignment::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'inactive', 'pending'])
            ->with('room')
            ->orderByRaw("CASE WHEN status = 'active' THEN 1 WHEN status = 'inactive' THEN 2 WHEN status = 'pending' THEN 3 END")
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

        // Get utility readings for tenant's room (last 6 months)
        $utilityReadings = collect();
        if ($currentAssignment && $currentAssignment->room_id) {
            $utilityReadings = UtilityReading::where('room_id', $currentAssignment->room_id)
                ->where(function ($query) use ($tenant) {
                    $query->where('tenant_id', $tenant->id) // Tenant-specific readings
                          ->orWhereNull('tenant_id'); // Room-level readings (shared/split)
                })
                ->whereHas('utilityType') // Only include readings with valid utility types
                ->with(['utilityType', 'room'])
                ->orderBy('reading_date', 'desc')
                ->take(10)
                ->get();
        }

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
            'maintenanceStats' => $maintenanceStats,
            'utilityReadings' => $utilityReadings,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Moved to main content area
        ];
    }
}
