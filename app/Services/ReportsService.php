<?php

namespace App\Services;

use App\Models\Room;
use App\Models\User;
use App\Models\Bill;
use App\Models\MaintenanceRequest;
use App\Models\RoomAssignment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportsService
{
    /**
     * Get occupancy report data
     */
    public function getOccupancyReport(string $period = 'monthly', ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subYear();
        $endDate = $endDate ?? Carbon::now();

        $totalRooms = Room::count();
        $currentOccupancy = Room::where('current_occupants', '>', 0)->count();
        $occupancyRate = $totalRooms > 0 ? round(($currentOccupancy / $totalRooms) * 100, 2) : 0;

        // Historical occupancy data
        $historicalData = $this->getHistoricalOccupancy($period, $startDate, $endDate);

        // Room type breakdown
        $roomTypeBreakdown = Room::select('type')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN current_occupants > 0 THEN 1 ELSE 0 END) as occupied')
            ->groupBy('type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'total' => $item->total,
                    'occupied' => $item->occupied,
                    'available' => $item->total - $item->occupied,
                    'occupancy_rate' => $item->total > 0 ? round(($item->occupied / $item->total) * 100, 2) : 0
                ];
            });

        // Average occupancy duration
        $avgDuration = RoomAssignment::whereNotNull('end_date')
            ->selectRaw('AVG(DATEDIFF(end_date, start_date)) as avg_days')
            ->value('avg_days') ?? 0;

        return [
            'summary' => [
                'total_rooms' => $totalRooms,
                'current_occupancy' => $currentOccupancy,
                'available_rooms' => $totalRooms - $currentOccupancy,
                'occupancy_rate' => $occupancyRate,
                'avg_duration_days' => round($avgDuration, 1)
            ],
            'historical_data' => $historicalData,
            'room_type_breakdown' => $roomTypeBreakdown,
            'period' => $period,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get financial report data
     */
    public function getFinancialReport(string $period = 'monthly', ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subYear();
        $endDate = $endDate ?? Carbon::now();

        // Revenue summary
        $totalRevenue = Bill::where('status', 'paid')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->sum('total_amount');

        $pendingRevenue = Bill::whereIn('status', ['unpaid', 'partially_paid'])
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->sum(DB::raw('total_amount - amount_paid'));

        $penaltyRevenue = Bill::where('penalty_amount', '>', 0)
            ->where('penalty_waived', false)
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->sum('penalty_amount');

        // Revenue by bill type
        $revenueByType = Bill::select('bill_type')
            ->selectRaw('SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as paid_amount')
            ->selectRaw('SUM(CASE WHEN status != "paid" THEN total_amount - amount_paid ELSE 0 END) as pending_amount')
            ->selectRaw('COUNT(*) as bill_count')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->groupBy('bill_type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->bill_type,
                    'paid_amount' => (float) $item->paid_amount,
                    'pending_amount' => (float) $item->pending_amount,
                    'total_amount' => (float) $item->paid_amount + (float) $item->pending_amount,
                    'bill_count' => $item->bill_count
                ];
            });

        // Monthly revenue trend
        $monthlyRevenue = $this->getMonthlyRevenueTrend($startDate, $endDate);

        // Payment status breakdown
        $paymentStatus = Bill::select('status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('SUM(amount_paid) as amount_paid')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'total_amount' => (float) $item->total_amount,
                    'amount_paid' => (float) $item->amount_paid,
                    'balance' => (float) $item->total_amount - (float) $item->amount_paid
                ];
            });

        return [
            'summary' => [
                'total_revenue' => $totalRevenue,
                'pending_revenue' => $pendingRevenue,
                'penalty_revenue' => $penaltyRevenue,
                'collection_rate' => $totalRevenue + $pendingRevenue > 0 ? 
                    round(($totalRevenue / ($totalRevenue + $pendingRevenue)) * 100, 2) : 0
            ],
            'revenue_by_type' => $revenueByType,
            'monthly_trend' => $monthlyRevenue,
            'payment_status' => $paymentStatus,
            'period' => $period,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get maintenance report data
     */
    public function getMaintenanceReport(string $period = 'monthly', ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->subYear();
        $endDate = $endDate ?? Carbon::now();

        // Summary statistics
        $totalRequests = MaintenanceRequest::whereBetween('created_at', [$startDate, $endDate])->count();
        $completedRequests = MaintenanceRequest::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $pendingRequests = MaintenanceRequest::where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Average resolution time
        $avgResolutionTime = MaintenanceRequest::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours')
            ->value('avg_hours') ?? 0;

        // Requests by priority
        $requestsByPriority = MaintenanceRequest::select('priority')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
            ->selectRaw('AVG(CASE WHEN status = "completed" 
                        THEN TIMESTAMPDIFF(HOUR, created_at, updated_at) ELSE NULL END) as avg_resolution_hours')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('priority')
            ->get()
            ->map(function ($item) {
                return [
                    'priority' => $item->priority,
                    'total_count' => $item->count,
                    'completed_count' => $item->completed,
                    'completion_rate' => $item->count > 0 ? round(($item->completed / $item->count) * 100, 2) : 0,
                    'avg_resolution_hours' => round($item->avg_resolution_hours ?? 0, 1)
                ];
            });

        // Requests by status
        $requestsByStatus = MaintenanceRequest::select('status')
            ->selectRaw('COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count
                ];
            });

        // Monthly request trend
        $monthlyTrend = $this->getMaintenanceMonthlyTrend($startDate, $endDate);

        // Most common issues (by area/description keywords)
        $commonIssues = MaintenanceRequest::select('area')
            ->selectRaw('COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('area')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'area' => $item->area,
                    'count' => $item->count
                ];
            });

        return [
            'summary' => [
                'total_requests' => $totalRequests,
                'completed_requests' => $completedRequests,
                'pending_requests' => $pendingRequests,
                'completion_rate' => $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0,
                'avg_resolution_hours' => round($avgResolutionTime, 1)
            ],
            'requests_by_priority' => $requestsByPriority,
            'requests_by_status' => $requestsByStatus,
            'monthly_trend' => $monthlyTrend,
            'common_issues' => $commonIssues,
            'period' => $period,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];
    }

    /**
     * Get historical occupancy data
     */
    private function getHistoricalOccupancy(string $period, Carbon $startDate, Carbon $endDate): Collection
    {
        // For now, we'll simulate historical data based on current room assignments
        // In a real scenario, you'd track this data over time
        $data = collect();
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $occupiedCount = RoomAssignment::where('start_date', '<=', $current)
                ->where(function ($query) use ($current) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', $current);
                })
                ->count();

            $data->push([
                'date' => $current->format('Y-m-d'),
                'occupied_rooms' => $occupiedCount,
                'occupancy_rate' => Room::count() > 0 ? round(($occupiedCount / Room::count()) * 100, 2) : 0
            ]);

            $current = $period === 'weekly' ? $current->addWeek() : $current->addMonth();
        }

        return $data;
    }

    /**
     * Get monthly revenue trend
     */
    private function getMonthlyRevenueTrend(Carbon $startDate, Carbon $endDate): Collection
    {
        return Bill::selectRaw('DATE_FORMAT(bill_date, "%Y-%m") as month')
            ->selectRaw('SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END) as revenue')
            ->selectRaw('SUM(CASE WHEN status != "paid" THEN total_amount - amount_paid ELSE 0 END) as pending')
            ->selectRaw('COUNT(*) as bill_count')
            ->whereBetween('bill_date', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'revenue' => (float) $item->revenue,
                    'pending' => (float) $item->pending,
                    'bill_count' => $item->bill_count
                ];
            });
    }

    /**
     * Get maintenance monthly trend
     */
    private function getMaintenanceMonthlyTrend(Carbon $startDate, Carbon $endDate): Collection
    {
        return MaintenanceRequest::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total_requests')
            ->selectRaw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_requests')
            ->selectRaw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_requests')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'total_requests' => $item->total_requests,
                    'completed_requests' => $item->completed_requests,
                    'pending_requests' => $item->pending_requests,
                    'completion_rate' => $item->total_requests > 0 ? 
                        round(($item->completed_requests / $item->total_requests) * 100, 2) : 0
                ];
            });
    }

    /**
     * Generate comprehensive dashboard summary
     */
    public function getDashboardSummary(): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $occupancy = $this->getOccupancyReport('monthly', $startDate, $endDate);
        $financial = $this->getFinancialReport('monthly', $startDate, $endDate);
        $maintenance = $this->getMaintenanceReport('monthly', $startDate, $endDate);

        return [
            'occupancy' => $occupancy['summary'],
            'financial' => $financial['summary'],
            'maintenance' => $maintenance['summary'],
            'generated_at' => now()->toDateTimeString()
        ];
    }
}