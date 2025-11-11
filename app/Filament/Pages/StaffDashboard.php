<?php

namespace App\Filament\Pages;

use App\Models\MaintenanceRequest;
use App\Models\Complaint;
use App\Models\Room;
use App\Models\RoomAssignment;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class StaffDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.staff-dashboard';
    protected static ?string $title = 'Dashboard';
    protected static ?string $slug = 'staff-dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'staff';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'staff';
    }

    public function getMyMaintenanceRequestsProperty()
    {
        return MaintenanceRequest::where('assigned_to', Auth::id())
            ->with(['tenant', 'room'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMyComplaintsProperty()
    {
        return Complaint::where('assigned_to', Auth::id())
            ->with(['tenant', 'room'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMaintenanceStatsProperty()
    {
        $requests = $this->myMaintenanceRequests;
        return [
            'total' => $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'in_progress' => $requests->where('status', 'in_progress')->count(),
            'completed' => $requests->where('status', 'completed')->count(),
        ];
    }

    public function getComplaintStatsProperty()
    {
        $complaints = $this->myComplaints;
        return [
            'total' => $complaints->count(),
            'open' => $complaints->where('status', 'open')->count(),
            'in_progress' => $complaints->where('status', 'in_progress')->count(),
            'resolved' => $complaints->where('status', 'resolved')->count(),
        ];
    }

    public function getRoomStatsProperty()
    {
        return [
            'total_rooms' => Room::count(),
            // Count rooms that have any occupants (current_occupants > 0)
            'occupied_rooms' => Room::where('current_occupants', '>', 0)->count(),
            // Count rooms with available space (current occupancy less than capacity)
            'available_rooms' => Room::whereColumn('current_occupants', '<', 'capacity')->count(),
            'total_tenants' => RoomAssignment::where('status', 'active')->count(),
        ];
    }

    public function getRecentMaintenanceProperty()
    {
        return $this->myMaintenanceRequests->take(5);
    }

    public function getRecentComplaintsProperty()
    {
        return $this->myComplaints->take(5);
    }

    public function getUrgentTasksProperty()
    {
        $urgentMaintenance = $this->myMaintenanceRequests
            ->whereIn('priority', ['high', 'urgent'])
            ->where('status', '!=', 'completed');
            
        $urgentComplaints = $this->myComplaints
            ->whereIn('priority', ['high', 'urgent'])
            ->where('status', '!=', 'resolved');

        return [
            'maintenance' => $urgentMaintenance,
            'complaints' => $urgentComplaints,
            'total' => $urgentMaintenance->count() + $urgentComplaints->count(),
        ];
    }
}
