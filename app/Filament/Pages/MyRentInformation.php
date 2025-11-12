<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomAssignment;
use App\Models\Bill;
use App\Models\UtilityReading;
use Carbon\Carbon;

class MyRentInformation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'My Rent Information';
    
    protected static ?string $navigationGroup = 'Rent Information';
    
    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.my-rent-information';
    
    protected static ?string $title = 'My Rent Information';

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
                'hasActiveAssignment' => false,
                'assignment' => null,
                'room' => null,
                'monthlyRent' => 0,
                'startDate' => null,
                'dueDate' => null,
                'electricityCharges' => 0,
                'waterCharges' => 0,
                'totalDue' => 0,
                'outstandingBalance' => 0,
                'latestBill' => null,
            ];
        }
        
        // Get active room assignment with relationships
        $assignment = RoomAssignment::where('tenant_id', $tenant->id)
            ->where('status', 'Active')
            ->with(['room', 'tenant'])
            ->first();

        if (!$assignment) {
            return [
                'hasActiveAssignment' => false,
                'assignment' => null,
                'room' => null,
                'monthlyRent' => 0,
                'startDate' => null,
                'dueDate' => null,
                'electricityCharges' => 0,
                'waterCharges' => 0,
                'totalDue' => 0,
                'outstandingBalance' => 0,
                'latestBill' => null,
            ];
        }

        // Get current month's utility readings
        $currentMonth = Carbon::now()->startOfMonth();
        
        // Get latest electricity reading
        $electricityReading = UtilityReading::where('room_id', $assignment->room_id)
            ->whereHas('utilityType', function($query) {
                $query->where('name', 'Electricity');
            })
            ->where('reading_date', '>=', $currentMonth)
            ->with('utilityType')
            ->orderBy('reading_date', 'desc')
            ->first();

        // Get latest water reading
        $waterReading = UtilityReading::where('room_id', $assignment->room_id)
            ->whereHas('utilityType', function($query) {
                $query->where('name', 'Water');
            })
            ->where('reading_date', '>=', $currentMonth)
            ->with('utilityType')
            ->orderBy('reading_date', 'desc')
            ->first();

        $electricityCharges = $electricityReading->price ?? 0;
        $waterCharges = $waterReading->price ?? 0;
        $monthlyRent = $assignment->monthly_rent ?? 0;

        // Get latest bill for due date and outstanding balance
        $latestBill = Bill::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Get outstanding balance (sum of unpaid bills)
        $outstandingBalance = Bill::where('tenant_id', $tenant->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->sum('total_amount');

        // Calculate due date (typically first day of next month or from latest bill)
        $dueDate = $latestBill && $latestBill->due_date 
            ? $latestBill->due_date 
            : Carbon::now()->addMonth()->startOfMonth();

        return [
            'hasActiveAssignment' => true,
            'assignment' => $assignment,
            'room' => $assignment->room,
            'monthlyRent' => $monthlyRent,
            'startDate' => $assignment->start_date,
            'dueDate' => $dueDate,
            'electricityCharges' => $electricityCharges,
            'waterCharges' => $waterCharges,
            'totalDue' => $monthlyRent + $electricityCharges + $waterCharges,
            'outstandingBalance' => $outstandingBalance,
            'latestBill' => $latestBill,
            'electricityReading' => $electricityReading,
            'waterReading' => $waterReading,
        ];
    }
}
