<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use App\Models\Tenant;
use App\Models\Bill;
use App\Models\RoomAssignment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class RoomOccupancyWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getCards(): array
    {
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();
        $maintenanceRooms = Room::where('status', 'maintenance')->count();
        $totalTenants = Tenant::count();
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;
        
        // Monthly revenue calculation
        $monthlyRevenue = Bill::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'paid')
            ->sum('amount');

        return [
            Card::make('Total Rooms', $totalRooms)
                ->description('Total number of rooms')
                ->descriptionIcon('heroicon-s-office-building')
                ->color('primary'),
                
            Card::make('Occupied Rooms', $occupiedRooms)
                ->description('Currently occupied')
                ->descriptionIcon('heroicon-s-users')
                ->color('success'),
                
            Card::make('Available Rooms', $availableRooms)
                ->description('Ready for assignment')
                ->descriptionIcon('heroicon-s-check-circle')
                ->color('warning'),
                
            Card::make('Occupancy Rate', $occupancyRate . '%')
                ->description('Current occupancy percentage')
                ->descriptionIcon('heroicon-s-chart-bar')
                ->color($occupancyRate > 80 ? 'success' : ($occupancyRate > 60 ? 'warning' : 'danger')),
                
            Card::make('Total Tenants', $totalTenants)
                ->description('Registered tenants')
                ->descriptionIcon('heroicon-s-users')
                ->color('info'),
                
            Card::make('Monthly Revenue', '₱' . number_format($monthlyRevenue, 2))
                ->description('Revenue this month')
                ->descriptionIcon('heroicon-s-cash')
                ->color('success'),
        ];
    }
}
