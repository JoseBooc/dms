<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use App\Models\Bill;
use App\Models\MaintenanceRequest;
use App\Models\PenaltySetting;

class Dashboard extends Page
{
    protected static string $view = 'filament.pages.dashboard';
    
    protected static ?string $title = 'Dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public function mount(): void
    {
        // Redirect non-admin users
        if (Auth::user()?->role !== 'admin') {
            $role = Auth::user()?->role;
            if ($role === 'tenant') {
                redirect('/dashboard/tenant-dashboard');
            } elseif ($role === 'staff') {
                redirect('/dashboard/staff-dashboard');
            }
        }
    }

    public function getTotalRoomsProperty()
    {
        return Room::count();
    }

    public function getOccupiedRoomsProperty()
    {
        // Count rooms at FULL capacity only (current_occupants >= capacity)
        return Room::whereColumn('current_occupants', '>=', 'capacity')->count();
    }

    public function getAvailableRoomsProperty()
    {
        // Count rooms with available space (current occupancy less than capacity)
        // Including partially occupied rooms
        return Room::whereColumn('current_occupants', '<', 'capacity')
                    ->where('is_hidden', false)
                    ->count();
    }

    public function getTotalTenantsProperty()
    {
        return User::where('role', 'tenant')->count();
    }

    public function getOccupancyRateProperty()
    {
        if ($this->totalRooms === 0) {
            return 0;
        }
        return round(($this->occupiedRooms / $this->totalRooms) * 100, 1);
    }

    public function getUnpaidBillsProperty()
    {
        return Bill::whereIn('status', ['unpaid', 'partially_paid'])->count();
    }

    public function getMonthlyRevenueProperty()
    {
        // Calculate revenue excluding water and electricity (pass-through costs)
        // Revenue = room_rate + other_charges + penalty_charge + penalty_amount
        // This represents actual profit, not total collections
        return Bill::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum(DB::raw('COALESCE(room_rate, 0) + COALESCE(other_charges, 0) + COALESCE(penalty_charge, 0) + COALESCE(penalty_amount, 0)'));
    }

    public function getPendingMaintenanceProperty()
    {
        return MaintenanceRequest::whereIn('status', ['pending', 'in_progress'])->count();
    }

    public function getRecentBillsProperty()
    {
        return Bill::with(['tenant', 'room'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getRecentMaintenanceProperty()
    {
        return MaintenanceRequest::with(['tenant', 'room'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    // Penalty Settings Properties
    public function getActivePenaltySettingProperty()
    {
        return PenaltySetting::where('active', true)
            ->where('name', 'late_payment_penalty')
            ->first();
    }

    public function getPenaltyTypeDisplayProperty()
    {
        $setting = $this->activePenaltySetting;
        if (!$setting) return 'Not Configured';

        return match($setting->penalty_type) {
            'daily_fixed' => 'Daily Fixed',
            'percentage' => 'Percentage',
            'flat_fee' => 'Flat Fee',
            default => 'Unknown'
        };
    }

    public function getPenaltyRateDisplayProperty()
    {
        $setting = $this->activePenaltySetting;
        if (!$setting) return '₱0';

        return match($setting->penalty_type) {
            'daily_fixed' => '₱' . number_format($setting->penalty_rate, 2) . '/day',
            'percentage' => number_format($setting->penalty_rate, 1) . '%',
            'flat_fee' => '₱' . number_format($setting->penalty_rate, 2),
            default => '₱0'
        };
    }

    public function getGracePeriodDisplayProperty()
    {
        $setting = $this->activePenaltySetting;
        if (!$setting) return '0 days';

        $days = $setting->grace_period_days ?? 0;
        return $days . ' ' . ($days === 1 ? 'day' : 'days');
    }

    public function getMaxPenaltyDisplayProperty()
    {
        $setting = $this->activePenaltySetting;
        if (!$setting || !$setting->max_penalty) return 'No Cap';

        return '₱' . number_format($setting->max_penalty, 2);
    }

    public function getOverdueBillsProperty()
    {
        // Get active penalty setting to determine grace period
        $setting = $this->activePenaltySetting;
        $gracePeriodDays = $setting ? $setting->grace_period_days : 0;
        
        // Bills are overdue if due_date + grace_period < today
        $overdueDate = now()->subDays($gracePeriodDays)->startOfDay();
        
        return Bill::where('status', '!=', 'paid')
            ->where('due_date', '<', $overdueDate)
            ->count();
    }

    public function getOverdueBillsWithinGracePeriodProperty()
    {
        // Bills past due date but still within grace period
        $setting = $this->activePenaltySetting;
        if (!$setting) return 0;
        
        $gracePeriodDays = $setting->grace_period_days;
        $overdueDate = now()->subDays($gracePeriodDays)->startOfDay();
        
        return Bill::where('status', '!=', 'paid')
            ->where('due_date', '<', now()->startOfDay())
            ->where('due_date', '>=', $overdueDate)
            ->count();
    }

    public function getTotalPenaltiesCollectedProperty()
    {
        return Bill::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('penalty_amount');
    }
}
