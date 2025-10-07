<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomAssignment;
use App\Models\UtilityReading;
use App\Models\UtilityType;

class UtilityDetails extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    
    protected static ?string $navigationLabel = 'Utility Details';
    
    protected static ?string $navigationGroup = 'Rent Information';
    
    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.pages.utility-details';
    
    protected static ?string $title = 'Utility Details';

    public static function canAccess(): bool
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
                'utilityReadings' => collect(),
                'utilityTypes' => collect()
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
                'utilityReadings' => collect(),
                'utilityTypes' => collect()
            ];
        }

        // Get all utility types
        $utilityTypes = UtilityType::where('status', 'active')->get();

        // Get latest utility readings for this room
        $utilityReadings = UtilityReading::where('room_id', $currentAssignment->room_id)
            ->with('utilityType')
            ->orderBy('reading_date', 'desc')
            ->take(10)
            ->get()
            ->groupBy('utility_type_id');

        return [
            'currentAssignment' => $currentAssignment,
            'utilityReadings' => $utilityReadings,
            'utilityTypes' => $utilityTypes
        ];
    }
}
