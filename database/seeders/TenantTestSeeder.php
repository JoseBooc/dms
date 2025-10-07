<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RoomAssignment;

class TenantTestSeeder extends Seeder
{
    public function run()
    {
        // Create a test tenant user
        $user = User::firstOrCreate(
            ['email' => 'tenant@test.com'],
            [
                'name' => 'Test Tenant',
                'email' => 'tenant@test.com',
                'password' => Hash::make('password'),
                'role' => 'tenant',
                'email_verified_at' => now(),
            ]
        );

        // Create tenant record
        $tenant = Tenant::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'first_name' => 'Test',
                'last_name' => 'Tenant',
                'phone_number' => '09123456789',
                'personal_email' => 'tenant@test.com',
                'birth_date' => '1990-01-01',
                'gender' => 'male',
                'nationality' => 'Filipino',
                'civil_status' => 'single',
                'emergency_contact_first_name' => 'Emergency',
                'emergency_contact_last_name' => 'Contact',
                'emergency_contact_phone' => '09987654321',
                'emergency_contact_relationship' => 'parent',
            ]
        );

        // Create a test room if it doesn't exist
        $room = Room::firstOrCreate(
            ['room_number' => 'R001'],
            [
                'room_number' => 'R001',
                'floor' => 1,
                'room_type' => 'single',
                'capacity' => 2,
                'rent_amount' => 5000.00,
                'status' => 'occupied',
                'description' => 'Test room for tenant portal',
            ]
        );

        // Create room assignment
        RoomAssignment::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'status' => 'active'
            ],
            [
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'start_date' => now()->subMonths(1),
                'status' => 'active',
                'monthly_rent' => 5000.00,
            ]
        );

        $this->command->info('Test tenant user created: tenant@test.com / password');
    }
}