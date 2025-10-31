<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use App\Models\Bill;
use App\Models\MaintenanceRequest;

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
        return Room::whereHas('currentAssignments')->count();
    }

    public function getAvailableRoomsProperty()
    {
        return Room::where('status', 'available')
            ->whereDoesntHave('currentAssignments')
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
        return Bill::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
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
}
