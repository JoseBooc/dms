<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomAssignment;

class RoomInformation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Room Information';
    
    protected static ?string $navigationGroup = 'Rent Information';
    
    protected static ?int $navigationSort = 12;

    protected static string $view = 'filament.pages.room-information';
    
    protected static ?string $title = 'Room Information';

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
                'roommates' => collect(),
                'room' => null
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
                'roommates' => collect(),
                'room' => null
            ];
        }

        // Get roommates (other active tenants in the same room)
        $roommates = RoomAssignment::where('room_id', $currentAssignment->room_id)
            ->where('tenant_id', '!=', $tenant->id)
            ->where('status', 'active')
            ->with(['tenant.user'])
            ->get();

        return [
            'currentAssignment' => $currentAssignment,
            'roommates' => $roommates,
            'room' => $currentAssignment->room
        ];
    }
}
