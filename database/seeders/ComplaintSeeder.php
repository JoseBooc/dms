<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Room;

class ComplaintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenants = User::where('role', 'tenant')->get();
        $rooms = Room::all();
        $staffUsers = User::whereIn('role', ['admin', 'staff'])->get();

        if ($tenants->isEmpty() || $rooms->isEmpty()) {
            $this->command->info('No tenants or rooms found. Skipping complaint seeding.');
            return;
        }

        $complaints = [
            [
                'title' => 'Loud music from neighboring room',
                'description' => 'The tenant in room 102 has been playing loud music late at night, disrupting sleep.',
                'category' => 'noise',
                'priority' => 'medium',
                'status' => 'pending'
            ],
            [
                'title' => 'Broken air conditioning unit',
                'description' => 'The AC unit in my room is not working properly. It makes strange noises and does not cool the room.',
                'category' => 'maintenance',
                'priority' => 'high',
                'status' => 'investigating'
            ],
            [
                'title' => 'Dirty common bathroom',
                'description' => 'The shared bathroom on the 2nd floor has not been cleaned for several days.',
                'category' => 'cleanliness',
                'priority' => 'medium',
                'status' => 'pending'
            ],
            [
                'title' => 'Wi-Fi connectivity issues',
                'description' => 'Internet connection is very slow and frequently disconnects in room 205.',
                'category' => 'facilities',
                'priority' => 'medium',
                'status' => 'resolved'
            ],
            [
                'title' => 'Security concern - broken lock',
                'description' => 'The main entrance door lock is not working properly, compromising building security.',
                'category' => 'security',
                'priority' => 'urgent',
                'status' => 'investigating'
            ]
        ];

        foreach ($complaints as $complaintData) {
            Complaint::create([
                'tenant_id' => $tenants->random()->id,
                'room_id' => $rooms->random()->id,
                'title' => $complaintData['title'],
                'description' => $complaintData['description'],
                'category' => $complaintData['category'],
                'priority' => $complaintData['priority'],
                'status' => $complaintData['status'],
                'assigned_to' => $complaintData['status'] !== 'pending' ? $staffUsers->random()->id : null,
                'resolved_at' => $complaintData['status'] === 'resolved' ? now()->subDays(rand(1, 7)) : null,
            ]);
        }

        $this->command->info('Sample complaints created successfully!');
    }
}