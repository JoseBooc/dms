<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample staff users
        User::create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'name' => 'John Smith',
            'email' => 'staff@areja.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => 'active',
            'gender' => 'male',
        ]);

        User::create([
            'first_name' => 'Maria',
            'last_name' => 'Garcia',
            'name' => 'Maria Garcia',
            'email' => 'maria.garcia@areja.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => 'active',
            'gender' => 'female',
        ]);

        User::create([
            'first_name' => 'Robert',
            'last_name' => 'Johnson',
            'name' => 'Robert Johnson',
            'email' => 'robert.johnson@areja.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'status' => 'active',
            'gender' => 'male',
        ]);

        $this->command->info('Staff users created successfully!');
    }
}
