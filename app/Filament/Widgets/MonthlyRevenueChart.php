<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;

class MonthlyRevenueChart extends LineChartWidget
{
    protected static ?string $heading = 'Monthly Revenue Trend';
    
    protected static ?int $sort = 2;
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $months = [];
        $revenues = [];
        
        // Get data for the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $revenue = Bill::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', 'paid')
                ->sum('amount');
                
            $revenues[] = (float) $revenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₱)',
                    'data' => $revenues,
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
}
