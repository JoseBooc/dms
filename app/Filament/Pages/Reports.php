<?php

namespace App\Filament\Pages;

use App\Services\ReportsService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.reports';
    protected static ?string $title = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected ReportsService $reportsService;

    // Form data
    public $report_type = null;
    public $period = null;
    public $start_date;
    public $end_date;
    public $current_period_start;
    public $period_display = '';

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function boot(): void
    {
        $this->reportsService = app(ReportsService::class);
    }

    public function mount(): void
    {
        $this->period = null;
        $this->start_date = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(4)->schema([
                Select::make('report_type')
                    ->label('Report Type')
                    ->options([
                        'occupancy' => 'Occupancy Report',
                        'financial' => 'Financial Report',
                        'maintenance' => 'Maintenance Report',
                        'summary' => 'Dashboard Summary'
                    ])
                    ->placeholder('Select an option')
                    ->reactive(),

                Select::make('period')
                    ->label('Period')
                    ->options([
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly'
                    ])
                    ->placeholder('Select an option')
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->initializePeriod($state);
                    }),

                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required()
                    ->reactive()
                    ->hidden(fn () => !empty($this->period)),

                DatePicker::make('end_date')
                    ->label('End Date')
                    ->required()
                    ->reactive()
                    ->hidden(fn () => !empty($this->period)),

                Placeholder::make('period_navigation')
                    ->label('Period Navigation')
                    ->content(function () {
                        if (empty($this->period)) {
                            return '';
                        }
                        return view('components.period-navigation', [
                            'period' => $this->period,
                            'display' => $this->period_display ?: 'View',
                            'canGoPrevious' => true,
                            'canGoNext' => true
                        ]);
                    })
                    ->columnSpan(2)
                    ->hidden(fn () => empty($this->period)),
            ])
        ];
    }
    
    public function initializePeriod($period)
    {
        if (!$period) {
            $this->period = null;
            $this->period_display = '';
            return;
        }
        
        $this->period = $period;
        $this->current_period_start = Carbon::now();
        $this->updatePeriodDates();
    }
    
    public function updatePeriodDates()
    {
        $start = $this->current_period_start->copy();
        
        switch ($this->period) {
            case 'weekly':
                $this->start_date = $start->startOfWeek()->format('Y-m-d');
                $this->end_date = $start->copy()->endOfWeek()->format('Y-m-d');
                $this->period_display = $start->format('M j') . ' - ' . $start->copy()->endOfWeek()->format('M j, Y');
                break;
                
            case 'monthly':
                $this->start_date = $start->startOfMonth()->format('Y-m-d');
                $this->end_date = $start->copy()->endOfMonth()->format('Y-m-d');
                $this->period_display = $start->format('F Y');
                break;
                
            case 'quarterly':
                $this->start_date = $start->startOfQuarter()->format('Y-m-d');
                $this->end_date = $start->copy()->endOfQuarter()->format('Y-m-d');
                $quarter = ceil($start->month / 3);
                $this->period_display = 'Q' . $quarter . ' ' . $start->year;
                break;
                
            case 'yearly':
                $this->start_date = $start->startOfYear()->format('Y-m-d');
                $this->end_date = $start->copy()->endOfYear()->format('Y-m-d');
                $this->period_display = $start->format('Y');
                break;
        }
    }
    
    public function navigatePeriod($direction)
    {
        if (!$this->period || !$this->current_period_start) {
            return;
        }
        
        switch ($this->period) {
            case 'weekly':
                $this->current_period_start = $direction === 'next' 
                    ? $this->current_period_start->addWeek()
                    : $this->current_period_start->subWeek();
                break;
                
            case 'monthly':
                $this->current_period_start = $direction === 'next'
                    ? $this->current_period_start->addMonth()
                    : $this->current_period_start->subMonth();
                break;
                
            case 'quarterly':
                $this->current_period_start = $direction === 'next'
                    ? $this->current_period_start->addQuarter()
                    : $this->current_period_start->subQuarter();
                break;
                
            case 'yearly':
                $this->current_period_start = $direction === 'next'
                    ? $this->current_period_start->addYear()
                    : $this->current_period_start->subYear();
                break;
        }
        
        $this->updatePeriodDates();
    }

    protected function getActions(): array
    {
        return [
            \Filament\Pages\Actions\Action::make('reset_dates')
                ->label('Reset to Current Year')
                ->icon('heroicon-o-refresh')
                ->color('secondary')
                ->action(function () {
                    $this->resetDates();
                })
        ];
    }

    public function generateReport()
    {
        $this->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'required|string',
            'period' => 'required|string'
        ]);

        // The report will be generated automatically when the view re-renders
        Notification::make()
            ->title('Report Generated')
            ->success()
            ->send();
    }
    
    public function resetDates()
    {
        // Reset all form-related properties
        $this->period = null;
        $this->period_display = '';
        $this->current_period_start = null;
        
        // Reset dates to default values
        $this->start_date = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
        
        Notification::make()
            ->title('Dates Reset')
            ->body('Period reset to manual selection and dates reset to January 1, ' . Carbon::now()->year . ' through today')
            ->success()
            ->send();
    }

    public function getOccupancyReportData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return $this->reportsService->getOccupancyReport($this->period ?? 'daily', $startDate, $endDate);
    }

    public function getFinancialReportData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return $this->reportsService->getFinancialReport($this->period ?? 'daily', $startDate, $endDate);
    }

    public function getMaintenanceReportData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return $this->reportsService->getMaintenanceReport($this->period ?? 'daily', $startDate, $endDate);
    }

    public function getDashboardSummaryData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return $this->reportsService->getDashboardSummary($startDate, $endDate);
    }

    public function downloadCsv()
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        // Get data based on report type
        $data = $this->getReportData();
        
        // Generate CSV content
        $csv = $this->generateCsvContent($data);
        
        $filename = "{$this->report_type}_report_{$startDate->format('Y-m-d')}_to_{$endDate->format('Y-m-d')}.csv";
        
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
    
    protected function getReportData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return match($this->report_type) {
            'occupancy' => $this->getOccupancyReportData(),
            'financial' => $this->getFinancialReportData(),
            'maintenance' => $this->getMaintenanceReportData(),
            'summary' => $this->getDashboardSummaryData(),
            default => []
        };
    }
    
    protected function generateCsvContent(array $data): string
    {
        $csv = '';
        
        switch($this->report_type) {
            case 'occupancy':
                $csv .= "Occupancy Report\n";
                $csv .= "Period: {$this->start_date} to {$this->end_date}\n\n";
                $csv .= "Summary\n";
                $csv .= "Metric,Value\n";
                $csv .= "Total Rooms,{$data['summary']['total_rooms']}\n";
                $csv .= "Occupied Rooms,{$data['summary']['current_occupancy']}\n";
                $csv .= "Available Rooms,{$data['summary']['available_rooms']}\n";
                $csv .= "Occupancy Rate,{$data['summary']['occupancy_rate']}%\n";
                $csv .= "Average Duration,{$data['summary']['avg_duration_days']} days\n\n";
                
                $csv .= "Room Type Breakdown\n";
                $csv .= "Type,Total,Occupied,Available,Occupancy Rate\n";
                foreach ($data['room_type_breakdown'] as $room) {
                    $csv .= "{$room['type']},{$room['total']},{$room['occupied']},{$room['available']},{$room['occupancy_rate']}%\n";
                }
                break;
                
            case 'financial':
                $csv .= "Financial Report\n";
                $csv .= "Period: {$this->start_date} to {$this->end_date}\n\n";
                $csv .= "Summary\n";
                $csv .= "Metric,Amount\n";
                $csv .= "Total Revenue,₱" . number_format($data['summary']['total_revenue'], 2) . "\n";
                $csv .= "Pending Revenue,₱" . number_format($data['summary']['pending_revenue'], 2) . "\n";
                $csv .= "Penalty Revenue,₱" . number_format($data['summary']['penalty_revenue'], 2) . "\n";
                $csv .= "Collection Rate,{$data['summary']['collection_rate']}%\n\n";
                
                $csv .= "Revenue by Type\n";
                $csv .= "Type,Paid Amount,Pending Amount,Total Amount,Bill Count\n";
                foreach ($data['revenue_by_type'] as $type) {
                    $csv .= "{$type['type']},₱" . number_format($type['paid_amount'], 2) . ",₱" . number_format($type['pending_amount'], 2) . ",₱" . number_format($type['total_amount'], 2) . ",{$type['bill_count']}\n";
                }
                break;
                
            case 'maintenance':
                $csv .= "Maintenance Report\n";
                $csv .= "Period: {$this->start_date} to {$this->end_date}\n\n";
                $csv .= "Summary\n";
                $csv .= "Metric,Value\n";
                $csv .= "Total Requests,{$data['summary']['total_requests']}\n";
                $csv .= "Pending Requests,{$data['summary']['pending']}\n";
                $csv .= "In Progress,{$data['summary']['in_progress']}\n";
                $csv .= "Completed,{$data['summary']['completed']}\n";
                $csv .= "Average Completion Time,{$data['summary']['avg_completion_time']} days\n";
                $csv .= "Completion Rate,{$data['summary']['completion_rate']}%\n\n";
                
                $csv .= "Requests by Priority\n";
                $csv .= "Priority,Count,Percentage\n";
                foreach ($data['by_priority'] as $priority) {
                    $csv .= "{$priority['priority']},{$priority['count']},{$priority['percentage']}%\n";
                }
                break;
                
            case 'summary':
                $csv .= "Dashboard Summary Report\n";
                $csv .= "Period: {$this->start_date} to {$this->end_date}\n\n";
                $csv .= "Key Metrics\n";
                $csv .= "Metric,Value\n";
                $csv .= "Total Rooms,{$data['occupancy']['total_rooms']}\n";
                $csv .= "Occupied Rooms,{$data['occupancy']['occupied_rooms']}\n";
                $csv .= "Occupancy Rate,{$data['occupancy']['occupancy_rate']}%\n";
                $csv .= "Total Revenue,₱" . number_format($data['financial']['total_revenue'], 2) . "\n";
                $csv .= "Pending Revenue,₱" . number_format($data['financial']['pending_revenue'], 2) . "\n";
                $csv .= "Total Maintenance Requests,{$data['maintenance']['total_requests']}\n";
                $csv .= "Pending Maintenance,{$data['maintenance']['pending']}\n";
                break;
        }
        
        return $csv;
    }
}
