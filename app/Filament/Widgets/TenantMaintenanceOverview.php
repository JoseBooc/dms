<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\Auth;

class TenantMaintenanceOverview extends BaseWidget
{
    protected static ?int $sort = 4;

    public static function canView(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    protected function getCards(): array
    {
        $user = Auth::user();
        $tenant = $user?->tenant;
        
        if (!$tenant) {
            return [];
        }

        $totalRequests = MaintenanceRequest::where('tenant_id', $tenant->id)->count();
        $pendingRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
            ->where('status', 'pending')->count();
        $inProgressRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
            ->where('status', 'in_progress')->count();
        $completedRequests = MaintenanceRequest::where('tenant_id', $tenant->id)
            ->where('status', 'completed')->count();

        return [
            Card::make('Total Requests', $totalRequests)
                ->description('All maintenance requests')
                ->color('primary'),
                
            Card::make('Pending', $pendingRequests)
                ->description('Awaiting review')
                ->color($pendingRequests > 0 ? 'warning' : 'success'),
                
            Card::make('In Progress', $inProgressRequests)
                ->description('Currently being worked on')
                ->color($inProgressRequests > 0 ? 'primary' : 'secondary'),
                
            Card::make('Completed', $completedRequests)
                ->description('Finished repairs')
                ->color('success'),
        ];
    }
}
