<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceRequest;
use App\Models\Complaint;
use App\Models\Room;
use Filament\Widgets\PieChartWidget;

class DashboardOverview extends PieChartWidget
{
    protected static ?string $heading = 'Maintenance & Complaints Status';
    
    protected static ?int $sort = 3;
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get maintenance request counts by status
        $pendingMaintenance = MaintenanceRequest::where('status', 'pending')->count();
        $inProgressMaintenance = MaintenanceRequest::where('status', 'in_progress')->count();
        $completedMaintenance = MaintenanceRequest::where('status', 'completed')->count();
        
        // Get complaint counts by status
        $pendingComplaints = Complaint::where('status', 'pending')->count();
        $inProgressComplaints = Complaint::where('status', 'in_progress')->count();
        $resolvedComplaints = Complaint::where('status', 'resolved')->count();
        
        return [
            'datasets' => [
                [
                    'data' => [
                        $pendingMaintenance + $pendingComplaints,
                        $inProgressMaintenance + $inProgressComplaints, 
                        $completedMaintenance + $resolvedComplaints,
                    ],
                    'backgroundColor' => [
                        '#EF4444', // Red for pending
                        '#F59E0B', // Yellow for in progress
                        '#10B981', // Green for completed
                    ],
                    'borderColor' => [
                        '#DC2626',
                        '#D97706', 
                        '#059669',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                'Pending (' . ($pendingMaintenance + $pendingComplaints) . ')',
                'In Progress (' . ($inProgressMaintenance + $inProgressComplaints) . ')',
                'Completed (' . ($completedMaintenance + $resolvedComplaints) . ')',
            ],
        ];
    }
}
