<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PenaltySetting;
use Illuminate\Support\Facades\DB;

class DefaultPenaltySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Sets up realistic Philippine dormitory penalty rules
     *
     * @return void
     */
    public function run()
    {
        // Deactivate all existing settings
        DB::table('penalty_settings')->update(['active' => false]);
        
        // Update or create default Philippine dormitory penalty setting
        PenaltySetting::updateOrCreate(
            ['name' => 'late_payment_penalty'],
            [
                'description' => 'Late payment penalty for overdue bills - Philippine dormitory standard',
                'penalty_type' => 'daily_fixed',
                'penalty_rate' => 50.00, // ₱50 per day
                'grace_period_days' => 3, // 3 days grace period
                'max_penalty' => 500.00, // Cap at ₱500
                'active' => true,
            ]
        );
        
        $this->command->info('✓ Default Philippine dormitory penalty settings created successfully!');
        $this->command->info('  - Type: Daily Fixed (₱50/day)');
        $this->command->info('  - Grace Period: 3 days');
        $this->command->info('  - Maximum Penalty: ₱500');
    }
}
