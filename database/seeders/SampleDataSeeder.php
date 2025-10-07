<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\UtilityType;
use App\Models\UtilityRate;
use App\Models\UtilityReading;
use App\Models\Bill;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing sample data first
        $this->command->info('Clearing existing sample data...');
        
        // Delete in reverse order to respect foreign key constraints
        \App\Models\Bill::where('tenant_id', '>', 1)->delete(); // Keep admin bills if any
        \App\Models\UtilityReading::query()->delete();
        \App\Models\UtilityRate::query()->delete();
        \App\Models\UtilityType::query()->delete();
        \App\Models\RoomAssignment::query()->delete();
        \App\Models\Tenant::query()->delete();
        \App\Models\Room::where('room_number', '!=', 'R001')->delete(); // Keep the original R001
        \App\Models\User::where('role', 'tenant')->delete();

        $this->command->info('Creating sample data...');
        // Create Users for tenants
        $tenantUsers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'password' => bcrypt('password'),
                'role' => 'tenant',
                'status' => 'active',
                'gender' => 'male',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'password' => bcrypt('password'),
                'role' => 'tenant',
                'status' => 'active',
                'gender' => 'female',
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@example.com',
                'password' => bcrypt('password'),
                'role' => 'tenant',
                'status' => 'active',
                'gender' => 'male',
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@example.com',
                'password' => bcrypt('password'),
                'role' => 'tenant',
                'status' => 'active',
                'gender' => 'female',
            ]
        ];

        $users = [];
        foreach ($tenantUsers as $userData) {
            $users[] = User::create($userData);
        }

        // Create Tenants
        $tenants = [
            [
                'user_id' => $users[0]->id,
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Doe',
                'birth_date' => '1995-05-15',
                'gender' => 'male',
                'nationality' => 'Filipino',
                'occupation' => 'Software Engineer',
                'university' => 'University of the Philippines',
                'course' => 'Computer Science',
                'provincial_address' => 'Quezon City, Metro Manila',
                'phone_number' => '09171234567',
                'alternative_phone' => '09281234567',
                'personal_email' => 'john.doe@example.com',
                'current_address' => 'Manila, Metro Manila',
                'id_type' => 'Drivers License',
                'id_number' => 'N01-12-123456',
                'id_image_path' => 'images/ids/john_doe_id.jpg',
                'remarks' => 'Reliable tenant, always pays on time',
            ],
            [
                'user_id' => $users[1]->id,
                'first_name' => 'Jane',
                'middle_name' => 'Marie',
                'last_name' => 'Smith',
                'birth_date' => '1993-08-22',
                'gender' => 'female',
                'nationality' => 'Filipino',
                'occupation' => 'Nurse',
                'university' => 'University of Santo Tomas',
                'course' => 'Nursing',
                'provincial_address' => 'Cebu City, Cebu',
                'phone_number' => '09171234568',
                'alternative_phone' => '09281234568',
                'personal_email' => 'jane.smith@example.com',
                'current_address' => 'Makati, Metro Manila',
                'id_type' => 'Passport',
                'id_number' => 'P1234567',
                'id_image_path' => 'images/ids/jane_smith_id.jpg',
                'remarks' => 'Healthcare worker, very clean and organized',
            ],
            [
                'user_id' => $users[2]->id,
                'first_name' => 'Mike',
                'middle_name' => 'Anthony',
                'last_name' => 'Johnson',
                'birth_date' => '1990-12-10',
                'gender' => 'male',
                'nationality' => 'American',
                'occupation' => 'English Teacher',
                'university' => 'University of California',
                'course' => 'Education',
                'provincial_address' => 'Los Angeles, California, USA',
                'phone_number' => '09171234569',
                'alternative_phone' => '09281234569',
                'personal_email' => 'mike.johnson@example.com',
                'current_address' => 'BGC, Taguig',
                'id_type' => 'Passport',
                'id_number' => 'US1234567',
                'id_image_path' => 'images/ids/mike_johnson_id.jpg',
                'remarks' => 'Foreign teacher, speaks excellent English',
            ],
            [
                'user_id' => $users[3]->id,
                'first_name' => 'Sarah',
                'middle_name' => 'Grace',
                'last_name' => 'Wilson',
                'birth_date' => '1997-03-18',
                'gender' => 'female',
                'nationality' => 'Filipino',
                'occupation' => 'Marketing Specialist',
                'university' => 'Ateneo de Manila University',
                'course' => 'Marketing Management',
                'provincial_address' => 'Davao City, Davao del Sur',
                'phone_number' => '09171234570',
                'alternative_phone' => '09281234570',
                'personal_email' => 'sarah.wilson@example.com',
                'current_address' => 'Ortigas, Pasig',
                'id_type' => 'UMID',
                'id_number' => '1234-5678901-2',
                'id_image_path' => 'images/ids/sarah_wilson_id.jpg',
                'remarks' => 'Young professional, very social and friendly',
            ]
        ];

        $createdTenants = [];
        foreach ($tenants as $tenantData) {
            $createdTenants[] = Tenant::create($tenantData);
        }

        // Create additional rooms (we already have R001)
        $rooms = [
            [
                'room_number' => 'R002',
                'type' => 'double',
                'capacity' => 2,
                'rate' => 2500.00,
                'status' => 'occupied',
                'description' => 'Double room with shared bathroom',
                'current_occupants' => 1,
                'hidden' => false,
            ],
            [
                'room_number' => 'R003',
                'type' => 'single',
                'capacity' => 1,
                'rate' => 1800.00,
                'status' => 'occupied',
                'description' => 'Single room with private bathroom',
                'current_occupants' => 1,
                'hidden' => false,
            ],
            [
                'room_number' => 'R004',
                'type' => 'studio',
                'capacity' => 1,
                'rate' => 3000.00,
                'status' => 'occupied',
                'description' => 'Studio type with kitchenette',
                'current_occupants' => 1,
                'hidden' => false,
            ],
            [
                'room_number' => 'R005',
                'type' => 'double',
                'capacity' => 2,
                'rate' => 2200.00,
                'status' => 'available',
                'description' => 'Double room with balcony',
                'current_occupants' => 0,
                'hidden' => false,
            ]
        ];

        $createdRooms = [];
        // Get existing room
        $existingRoom = Room::where('room_number', 'R001')->first();
        if ($existingRoom) {
            $createdRooms[] = $existingRoom;
        }

        foreach ($rooms as $roomData) {
            $createdRooms[] = Room::create($roomData);
        }

        // Create Room Assignments
        $assignments = [
            [
                'room_id' => $createdRooms[0]->id, // R001
                'tenant_id' => $createdTenants[0]->id, // John Doe
                'start_date' => Carbon::now()->subMonths(6)->toDateString(),
                'end_date' => null,
                'monthly_rent' => 1500.00,
                'status' => 'active',
                'notes' => 'Long-term tenant, very reliable',
            ],
            [
                'room_id' => $createdRooms[1]->id, // R002
                'tenant_id' => $createdTenants[1]->id, // Jane Smith
                'start_date' => Carbon::now()->subMonths(3)->toDateString(),
                'end_date' => null,
                'monthly_rent' => 2500.00,
                'status' => 'active',
                'notes' => 'Healthcare worker, night shift schedule',
            ],
            [
                'room_id' => $createdRooms[2]->id, // R003
                'tenant_id' => $createdTenants[2]->id, // Mike Johnson
                'start_date' => Carbon::now()->subMonths(4)->toDateString(),
                'end_date' => null,
                'monthly_rent' => 1800.00,
                'status' => 'active',
                'notes' => 'English teacher, works at international school',
            ],
            [
                'room_id' => $createdRooms[3]->id, // R004
                'tenant_id' => $createdTenants[3]->id, // Sarah Wilson
                'start_date' => Carbon::now()->subMonths(2)->toDateString(),
                'end_date' => null,
                'monthly_rent' => 3000.00,
                'status' => 'active',
                'notes' => 'Young professional, works in marketing',
            ]
        ];

        foreach ($assignments as $assignmentData) {
            RoomAssignment::create($assignmentData);
        }

        // Create Utility Types
        $utilityTypes = [
            [
                'name' => 'Electricity',
                'unit' => 'kWh',
                'description' => 'Electrical consumption',
                'status' => 'active',
            ],
            [
                'name' => 'Water',
                'unit' => 'cubic meters',
                'description' => 'Water consumption',
                'status' => 'active',
            ],
            [
                'name' => 'Gas',
                'unit' => 'cubic meters',
                'description' => 'Gas consumption for cooking',
                'status' => 'active',
            ]
        ];

        $createdUtilityTypes = [];
        foreach ($utilityTypes as $utilityTypeData) {
            $createdUtilityTypes[] = UtilityType::create($utilityTypeData);
        }

        // Create Utility Rates
        $utilityRates = [
            [
                'utility_type_id' => $createdUtilityTypes[0]->id, // Electricity
                'rate_per_unit' => 12.50,
                'effective_from' => Carbon::now()->subMonths(12)->toDateString(),
                'effective_until' => null,
                'status' => 'active',
                'created_by' => 1, // Admin user
            ],
            [
                'utility_type_id' => $createdUtilityTypes[1]->id, // Water
                'rate_per_unit' => 25.00,
                'effective_from' => Carbon::now()->subMonths(12)->toDateString(),
                'effective_until' => null,
                'status' => 'active',
                'created_by' => 1, // Admin user
            ],
            [
                'utility_type_id' => $createdUtilityTypes[2]->id, // Gas
                'rate_per_unit' => 45.00,
                'effective_from' => Carbon::now()->subMonths(12)->toDateString(),
                'effective_until' => null,
                'status' => 'active',
                'created_by' => 1, // Admin user
            ]
        ];

        foreach ($utilityRates as $rateData) {
            UtilityRate::create($rateData);
        }

        // Create Utility Readings for the past 3 months
        $months = [
            Carbon::now()->subMonths(2),
            Carbon::now()->subMonth(),
            Carbon::now()
        ];

        foreach ($months as $month) {
            foreach ($createdRooms as $index => $room) {
                if ($index < 4) { // Only for occupied rooms
                    // Electricity readings
                    UtilityReading::create([
                        'room_id' => $room->id,
                        'utility_type_id' => $createdUtilityTypes[0]->id,
                        'current_reading' => rand(150, 300),
                        'previous_reading' => rand(100, 250),
                        'consumption' => rand(50, 100),
                        'reading_date' => $month->toDateString(),
                        'recorded_by' => 1,
                        'notes' => 'Regular monthly reading',
                    ]);

                    // Water readings
                    UtilityReading::create([
                        'room_id' => $room->id,
                        'utility_type_id' => $createdUtilityTypes[1]->id,
                        'current_reading' => rand(10, 25),
                        'previous_reading' => rand(5, 20),
                        'consumption' => rand(5, 15),
                        'reading_date' => $month->toDateString(),
                        'recorded_by' => 1,
                        'notes' => 'Regular monthly reading',
                    ]);
                }
            }
        }

        // Create Bills for tenants
        foreach ($createdTenants as $index => $tenant) {
            if ($index < 4) {
                $room = $createdRooms[$index];
                $user = $users[$index]; // Get the corresponding user
                
                // Room rental bills for past 2 months
                for ($i = 1; $i <= 2; $i++) {
                    Bill::create([
                        'tenant_id' => $user->id, // Use user ID instead of tenant ID
                        'room_id' => $room->id,
                        'bill_type' => 'room',
                        'description' => 'Monthly room rental',
                        'bill_date' => Carbon::now()->subMonths($i)->startOfMonth()->toDateString(),
                        'due_date' => Carbon::now()->subMonths($i)->addDays(15)->toDateString(),
                        'created_by' => 1, // Assuming admin user ID is 1
                        'room_rate' => $room->rate,
                        'electricity' => 0,
                        'water' => 0,
                        'other_charges' => 0,
                        'total_amount' => $room->rate,
                        'amount_paid' => $i == 1 ? 0 : $room->rate,
                        'status' => $i == 1 ? 'unpaid' : 'paid',
                        'created_by' => 1, // Admin user
                        'details' => json_encode([
                            'period' => Carbon::now()->subMonths($i)->format('F Y'),
                            'room_number' => $room->room_number,
                            'rate' => $room->rate
                        ]),
                    ]);
                }

                // Utility bills
                $utilityAmount = rand(500, 1500);
                Bill::create([
                    'tenant_id' => $user->id, // Use user ID instead of tenant ID
                    'room_id' => $room->id,
                    'bill_type' => 'utility',
                    'description' => 'Utility consumption',
                    'bill_date' => Carbon::now()->subMonth()->toDateString(),
                    'due_date' => Carbon::now()->addDays(15)->toDateString(),
                    'created_by' => 1, // Assuming admin user ID is 1
                    'room_rate' => 0,
                    'electricity' => rand(300, 800),
                    'water' => rand(100, 300),
                    'other_charges' => rand(50, 200),
                    'other_charges_description' => 'Maintenance fee',
                    'total_amount' => $utilityAmount,
                    'amount_paid' => 0,
                    'status' => 'unpaid',
                    'created_by' => 1, // Admin user
                    'details' => json_encode([
                        'electricity' => rand(50, 100) . ' kWh',
                        'water' => rand(5, 15) . ' cubic meters',
                        'period' => Carbon::now()->subMonth()->format('F Y')
                    ]),
                ]);
            }
        }

        $this->command->info('Sample data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- 4 tenant users with login credentials');
        $this->command->info('- 4 detailed tenant profiles');
        $this->command->info('- 5 rooms (including existing R001)');
        $this->command->info('- 4 active room assignments');
        $this->command->info('- 3 utility types with rates');
        $this->command->info('- Utility readings for past 3 months');
        $this->command->info('- Room rental and utility bills');
        $this->command->info('');
        $this->command->info('Tenant login credentials:');
        $this->command->info('john.doe@example.com / password');
        $this->command->info('jane.smith@example.com / password');
        $this->command->info('mike.johnson@example.com / password');
        $this->command->info('sarah.wilson@example.com / password');
    }
}
