<?php

namespace App\Filament\Pages;

use App\Services\ReportsService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
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
    public $report_type = 'occupancy';
    public $period = 'monthly';
    public $start_date;
    public $end_date;

    public function boot(): void
    {
        $this->reportsService = app(ReportsService::class);
    }

    public function mount(): void
    {
        $this->start_date = Carbon::now()->subMonths(6)->format('Y-m-d');
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
                    ->default('occupancy')
                    ->reactive(),

                Select::make('period')
                    ->label('Period')
                    ->options([
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'quarterly' => 'Quarterly',
                        'yearly' => 'Yearly'
                    ])
                    ->default('monthly'),

                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required()
                    ->default(Carbon::now()->subMonths(6)),

                DatePicker::make('end_date')
                    ->label('End Date')
                    ->required()
                    ->default(Carbon::now()),
            ])
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

    public function getOccupancyReportData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return $this->reportsService->getOccupancyReport($startDate, $endDate);
    }

    public function getFinancialReportData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return $this->reportsService->getFinancialReport($startDate, $endDate);
    }

    public function getMaintenanceReportData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return $this->reportsService->getMaintenanceReport($startDate, $endDate);
    }

    public function getDashboardSummaryData(): array
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        return $this->reportsService->getDashboardSummary($startDate, $endDate);
    }

    public function exportReport(string $format = 'csv')
    {
        // This would handle exporting the report
        // For now, we'll just show a notification
        Notification::make()
            ->title('Export Coming Soon!')
            ->body("Export to {$format} feature will be available in the next update.")
            ->warning()
            ->send();
    }
}
