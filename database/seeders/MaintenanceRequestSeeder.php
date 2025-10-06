<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\User;

class MaintenanceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenants = Tenant::all();
        $rooms = Room::all();
        $staffUsers = User::whereIn('role', ['admin', 'staff'])->get();

        if ($tenants->isEmpty() || $rooms->isEmpty()) {
            $this->command->info('No tenants or rooms found. Skipping maintenance request seeding.');
            return;
        }

        $maintenanceRequests = [
            [
                'area' => 'Bathroom',
                'description' => 'The shower head is not working properly. Water pressure is very low and sometimes no water comes out at all.',
                'priority' => 'high',
                'status' => 'pending'
            ],
            [
                'area' => 'Bedroom',
                'description' => 'The ceiling fan makes loud noises when running. It seems like the blades are unbalanced or the motor is failing.',
                'priority' => 'medium',
                'status' => 'in_progress'
            ],
            [
                'area' => 'Kitchen',
                'description' => 'The refrigerator is not cooling properly. Food items are spoiling faster than usual.',
                'priority' => 'high',
                'status' => 'pending'
            ],
            [
                'area' => 'Living Room',
                'description' => 'Two electrical outlets are not working. I cannot plug in any devices in the main living area.',
                'priority' => 'medium',
                'status' => 'completed'
            ],
            [
                'area' => 'Bathroom',
                'description' => 'The toilet is constantly running water. The flush mechanism seems to be stuck.',
                'priority' => 'medium',
                'status' => 'in_progress'
            ],
            [
                'area' => 'Bedroom',
                'description' => 'The window lock is broken and the window cannot be properly secured.',
                'priority' => 'high',
                'status' => 'pending'
            ],
            [
                'area' => 'Kitchen',
                'description' => 'The kitchen sink faucet is leaking continuously. Water bills might increase due to this.',
                'priority' => 'medium',
                'status' => 'completed'
            ],
            [
                'area' => 'Common Area',
                'description' => 'The light bulb in the hallway near room 205 is flickering and needs replacement.',
                'priority' => 'low',
                'status' => 'pending'
            ],
            [
                'area' => 'Bedroom',
                'description' => 'The air conditioning unit is making strange noises and not cooling the room efficiently.',
                'priority' => 'high',
                'status' => 'in_progress'
            ],
            [
                'area' => 'Bathroom',
                'description' => 'The bathroom door handle is loose and difficult to operate. Sometimes the door gets stuck.',
                'priority' => 'low',
                'status' => 'completed'
            ]
        ];

        foreach ($maintenanceRequests as $requestData) {
            $tenant = $tenants->random();
            $room = $rooms->random();
            
            MaintenanceRequest::create([
                'tenant_id' => $tenant->id,
                'room_id' => $room->id,
                'area' => $requestData['area'],
                'description' => $requestData['description'],
                'priority' => $requestData['priority'],
                'status' => $requestData['status'],
                'assigned_to' => $requestData['status'] !== 'pending' ? $staffUsers->random()->id : null,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);
        }

        $this->command->info('Sample maintenance requests created successfully!');
    }
}