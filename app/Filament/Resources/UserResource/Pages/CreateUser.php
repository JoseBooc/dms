<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Models\Room;
use App\Notifications\NewTenantNotification;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getCancelledRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $newUser = $this->record;
        
        // Only notify if the new user is a tenant
        if ($newUser->role === 'tenant') {
            // Try to find the assigned room (if any)
            $room = null;
            $assignment = $newUser->roomAssignments()->where('status', 'active')->first();
            if ($assignment) {
                $room = $assignment->room;
            }
            
            // Notify all admins about new tenant registration
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new NewTenantNotification($newUser, $room));
            }
        }
    }
}
