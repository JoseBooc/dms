<?php

namespace App\Filament\Pages;

use App\Models\Room;
use App\Models\RoomAssignment;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffRoomReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.staff-room-reports';
    protected static ?string $title = 'Room Occupancy Reports';
    protected static ?string $navigationLabel = 'Room Reports';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'staff';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'staff';
    }

    public function getRoomsProperty()
    {
        return Room::with(['currentAssignments.tenant'])
            ->orderBy('room_number')
            ->get();
    }

    public function getOccupiedRoomsProperty()
    {
        return $this->rooms->filter(function ($room) {
            return $room->currentAssignments->count() > 0;
        });
    }

    public function getVacantRoomsProperty()
    {
        return $this->rooms->filter(function ($room) {
            return $room->currentAssignments->count() === 0 && $room->status === 'available';
        });
    }

    public function getUnavailableRoomsProperty()
    {
        return $this->rooms->filter(function ($room) {
            return $room->status !== 'available';
        });
    }

    public function getOvercrowdedRoomsProperty()
    {
        return $this->rooms->filter(function ($room) {
            return $room->currentAssignments->count() > $room->capacity;
        });
    }

    public function getOccupancyStatsProperty()
    {
        $totalRooms = $this->rooms->count();
        $occupiedCount = $this->occupiedRooms->count();
        $vacantCount = $this->vacantRooms->count();
        $unavailableCount = $this->unavailableRooms->count();

        return [
            'total_rooms' => $totalRooms,
            'occupied' => $occupiedCount,
            'vacant' => $vacantCount,
            'unavailable' => $unavailableCount,
            'occupancy_rate' => $totalRooms > 0 ? round(($occupiedCount / $totalRooms) * 100, 1) : 0,
            'utilization_rate' => $totalRooms > 0 ? round((($occupiedCount + $unavailableCount) / $totalRooms) * 100, 1) : 0,
        ];
    }

    public function getTotalTenantsProperty()
    {
        return RoomAssignment::where('status', 'active')->count();
    }

    public function getCapacityUtilizationProperty()
    {
        $totalCapacity = $this->rooms->sum('capacity');
        $currentOccupants = $this->totalTenants;
        
        return [
            'total_capacity' => $totalCapacity,
            'current_occupants' => $currentOccupants,
            'utilization_percentage' => $totalCapacity > 0 ? round(($currentOccupants / $totalCapacity) * 100, 1) : 0,
            'available_spaces' => max(0, $totalCapacity - $currentOccupants),
        ];
    }
}
