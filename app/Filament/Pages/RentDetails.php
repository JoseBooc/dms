<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomAssignment;
use App\Models\Bill;

class RentDetails extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Rent Details';
    
    protected static ?string $navigationGroup = 'Rent Information';
    
    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.rent-details';
    
    protected static ?string $title = 'Rent Details';

    public static function canAccess(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->role === 'tenant';
    }

    public function getViewData(): array
    {
        $user = Auth::user();
        $tenant = $user->tenant;
        
        if (!$tenant) {
            return [
                'currentAssignment' => null,
                'latestBill' => null,
                'nextDueBill' => null,
                'monthlyRate' => 0
            ];
        }
        
        // Get current room assignment
        $currentAssignment = RoomAssignment::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('room')
            ->first();

        if (!$currentAssignment) {
            return [
                'currentAssignment' => null,
                'latestBill' => null,
                'nextDueBill' => null,
                'monthlyRate' => 0
            ];
        }

        // Get latest bill
        $latestBill = Bill::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get next due bill (unpaid bill with nearest due date)
        $nextDueBill = Bill::where('tenant_id', $tenant->id)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->first();

        return [
            'currentAssignment' => $currentAssignment,
            'latestBill' => $latestBill,
            'nextDueBill' => $nextDueBill,
            'monthlyRate' => $currentAssignment->monthly_rent ?? 0
        ];
    }
}
