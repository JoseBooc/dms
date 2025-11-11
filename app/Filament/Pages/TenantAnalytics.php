<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomAssignment;
use App\Models\Bill;
use App\Models\MaintenanceRequest;
use App\Models\Complaint;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TenantAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    
    protected static ?string $navigationLabel = 'My Tenancy Summary';
    
    protected static ?string $navigationGroup = 'Analytics';
    
    protected static ?int $navigationSort = 999;

    protected static string $view = 'filament.pages.tenant-analytics';
    
    protected static ?string $title = 'My Tenancy Summary';
    
    protected static ?string $slug = 'tenant-analytics';

    public $tenancyStats = [];
    public $financialStats = [];
    public $activityStats = [];
    public $currentAssignment;
    public $tenancyHistory = [];
    public $recentActivity = [];

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
        $this->loadAnalyticsData();
    }

    protected function loadAnalyticsData(): void
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return;
        }

        // Get current room assignment with room in single query
        $this->currentAssignment = RoomAssignment::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('room')
            ->first();

        // Calculate tenancy statistics
        $this->tenancyStats = $this->calculateTenancyStats($tenant);
        
        // Calculate financial statistics  
        $this->financialStats = $this->calculateFinancialStats($tenant);
        
        // Calculate activity statistics
        $this->activityStats = $this->calculateActivityStats($tenant);
        
        // Get tenancy history
        $this->tenancyHistory = $this->getTenancyHistory($tenant);
        
        // Get recent activity
        $this->recentActivity = $this->getRecentActivity($tenant);
    }

    protected function calculateTenancyStats($tenant): array
    {
        $assignments = RoomAssignment::where('tenant_id', $tenant->id)->get();
        
        $currentAssignment = $assignments->where('status', 'active')->first();
        $completedAssignments = $assignments->where('status', 'completed');
        
        $totalStayDays = 0;
        $currentStayDays = 0;
        
        foreach ($completedAssignments as $assignment) {
            if ($assignment->start_date && $assignment->end_date) {
                $totalStayDays += Carbon::parse($assignment->start_date)->diffInDays(Carbon::parse($assignment->end_date));
            }
        }
        
        if ($currentAssignment && $currentAssignment->start_date) {
            $currentStayDays = Carbon::parse($currentAssignment->start_date)->diffInDays(Carbon::now());
            $totalStayDays += $currentStayDays;
        }
        
        return [
            'total_assignments' => $assignments->count(),
            'current_room' => $currentAssignment?->room?->room_number ?? 'None',
            'current_status' => $currentAssignment?->status ?? 'No active assignment',
            'current_stay_days' => $currentStayDays,
            'current_stay_months' => round($currentStayDays / 30, 1),
            'total_stay_days' => $totalStayDays,
            'total_stay_months' => round($totalStayDays / 30, 1),
            'start_date' => $currentAssignment?->start_date ? Carbon::parse($currentAssignment->start_date)->format('M d, Y') : 'N/A',
            'member_since' => $tenant->created_at->format('M d, Y'),
        ];
    }

    protected function calculateFinancialStats($tenant): array
    {
        // Bills reference users table via tenant_id - fetch once and cache
        $bills = Bill::where('tenant_id', $tenant->user_id)
            ->select('id', 'tenant_id', 'total_amount', 'penalty_amount', 'status', 'bill_date', 'due_date')
            ->get();
        
        $totalBilled = $bills->sum(function($bill) {
            return $bill->total_amount + ($bill->penalty_amount ?? 0);
        });
        
        $totalPaid = $bills->where('status', 'paid')->sum(function($bill) {
            return $bill->total_amount + ($bill->penalty_amount ?? 0);
        });
        
        $currentMonthBills = $bills->filter(function($bill) {
            return Carbon::parse($bill->bill_date)->isCurrentMonth();
        });
        $currentMonthAmount = $currentMonthBills->sum('total_amount');
        
        $overdueAmount = $bills->where('status', '!=', 'paid')->filter(function($bill) {
            return $bill->isOverdue();
        })->sum(function($bill) {
            return $bill->total_amount + ($bill->penalty_amount ?? 0);
        });
        
        return [
            'total_billed' => $totalBilled,
            'total_paid' => $totalPaid,
            'current_month_amount' => $currentMonthAmount,
            'overdue_amount' => $overdueAmount,
            'payment_rate' => $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 0,
            'total_bills' => $bills->count(),
            'paid_bills' => $bills->where('status', 'paid')->count(),
            'overdue_bills' => $bills->filter(function($bill) { return $bill->isOverdue(); })->count(),
        ];
    }

    protected function calculateActivityStats($tenant): array
    {
        // Fetch only required columns to reduce memory usage
        $maintenanceRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
            ->select('id', 'tenant_id', 'status', 'created_at', 'updated_at')
            ->get();
        
        $complaints = Complaint::where('tenant_id', $tenant->user_id)
            ->select('id', 'tenant_id', 'status', 'created_at', 'resolved_at')
            ->get();
        
        return [
            'total_maintenance_requests' => $maintenanceRequests->count(),
            'completed_maintenance' => $maintenanceRequests->where('status', 'completed')->count(),
            'pending_maintenance' => $maintenanceRequests->whereIn('status', ['pending', 'in_progress'])->count(),
            'total_complaints' => $complaints->count(),
            'resolved_complaints' => $complaints->where('status', 'resolved')->count(),
            'pending_complaints' => $complaints->whereIn('status', ['open', 'investigating'])->count(),
            'avg_resolution_days' => $this->calculateAvgResolutionDays($maintenanceRequests, $complaints),
        ];
    }

    protected function calculateAvgResolutionDays($maintenanceRequests, $complaints): float
    {
        $resolvedMaintenanceRequests = $maintenanceRequests->where('status', 'completed')->filter(function($request) {
            return $request->created_at && $request->updated_at;
        });
        
        $resolvedComplaints = $complaints->where('status', 'resolved')->filter(function($complaint) {
            return $complaint->created_at && $complaint->resolved_at;
        });
        
        $totalDays = 0;
        $totalItems = 0;
        
        foreach ($resolvedMaintenanceRequests as $request) {
            $totalDays += $request->created_at->diffInDays($request->updated_at);
            $totalItems++;
        }
        
        foreach ($resolvedComplaints as $complaint) {
            $totalDays += $complaint->created_at->diffInDays($complaint->resolved_at);
            $totalItems++;
        }
        
        return $totalItems > 0 ? round($totalDays / $totalItems, 1) : 0;
    }

    protected function getTenancyHistory($tenant): array
    {
        return RoomAssignment::where('tenant_id', $tenant->id)
            ->with('room')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($assignment) {
                $startDate = Carbon::parse($assignment->start_date);
                $endDate = $assignment->end_date ? Carbon::parse($assignment->end_date) : Carbon::now();
                $duration = $startDate->diffInDays($endDate);
                
                return [
                    'room_number' => $assignment->room->room_number,
                    'start_date' => $startDate->format('M d, Y'),
                    'end_date' => $assignment->end_date ? Carbon::parse($assignment->end_date)->format('M d, Y') : 'Current',
                    'duration_days' => $duration,
                    'duration_months' => round($duration / 30, 1),
                    'status' => $assignment->status,
                ];
            })
            ->toArray();
    }

    protected function getRecentActivity($tenant): array
    {
        $activities = collect();
        
        // Get recent bills - only select needed columns
        $recentBills = Bill::where('tenant_id', $tenant->user_id)
            ->select('id', 'tenant_id', 'bill_type', 'total_amount', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        foreach ($recentBills as $bill) {
            $activities->push([
                'type' => 'bill',
                'title' => "Bill #{$bill->id} - " . ucfirst($bill->bill_type ?? 'General'),
                'description' => "Amount: ₱" . number_format($bill->total_amount, 2),
                'date' => $bill->created_at->format('M d, Y'),
                'status' => $bill->status,
                'icon' => 'heroicon-o-cash',
                'color' => $bill->status === 'paid' ? 'success' : ($bill->status === 'overdue' ? 'danger' : 'warning'),
            ]);
        }
        
        // Get recent maintenance requests - only select needed columns
        $recentMaintenance = MaintenanceRequest::where('tenant_id', $tenant->id)
            ->select('id', 'tenant_id', 'description', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        foreach ($recentMaintenance as $request) {
            $activities->push([
                'type' => 'maintenance',
                'title' => "Maintenance Request #{$request->id}",
                'description' => $request->description,
                'date' => $request->created_at->format('M d, Y'),
                'status' => $request->status,
                'icon' => 'heroicon-o-cog',
                'color' => $request->status === 'completed' ? 'success' : ($request->status === 'in_progress' ? 'warning' : 'gray'),
            ]);
        }
        
        // Get recent complaints - only select needed columns
        $recentComplaints = Complaint::where('tenant_id', $tenant->user_id)
            ->select('id', 'tenant_id', 'title', 'category', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        foreach ($recentComplaints as $complaint) {
            $activities->push([
                'type' => 'complaint',
                'title' => $complaint->title,
                'description' => $complaint->category,
                'date' => $complaint->created_at->format('M d, Y'),
                'status' => $complaint->status,
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => $complaint->status === 'resolved' ? 'success' : ($complaint->status === 'investigating' ? 'warning' : 'danger'),
            ]);
        }
        
        return $activities->sortByDesc('date')->take(10)->values()->toArray();
    }
}