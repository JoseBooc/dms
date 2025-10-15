<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UtilityType;

class UtilityTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Electricity utility type
        UtilityType::updateOrCreate(
            ['name' => 'Electricity'],
            [
                'unit' => 'kWh',
                'description' => 'Electrical power consumption measurement',
                'status' => 'active'
            ]
        );

        // Create Water utility type
        UtilityType::updateOrCreate(
            ['name' => 'Water'],
            [
                'unit' => 'cu. m.',
                'description' => 'Water consumption measurement',
                'status' => 'active'
            ]
        );
    }
}
