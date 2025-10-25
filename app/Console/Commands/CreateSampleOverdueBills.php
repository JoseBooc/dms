<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Tenant;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateSampleOverdueBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:create-overdue-samples {--count=3 : Number of tenants to create bills for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create sample overdue bills for testing the penalty system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        
        $this->info('Creating test overdue bills...');

        // Get some tenants and rooms
        $tenants = Tenant::take($count)->get();
        $rooms = Room::take($count)->get();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found. Please create some tenants first.');
            $this->line('You can run: php artisan create-sample-data.php');
            return 1;
        }

        if ($rooms->isEmpty()) {
            $this->error('No rooms found. Please create some rooms first.');
            return 1;
        }

        $this->info("Found {$tenants->count()} tenants and {$rooms->count()} rooms.");

        $billsCreated = 0;

        foreach ($tenants as $index => $tenant) {
            $room = $rooms[$index] ?? $rooms->first();
            
            $this->line("Creating bills for tenant: {$tenant->name} (User ID: {$tenant->user_id})");
            
            // Create overdue rent bill
            Bill::create([
                'tenant_id' => $tenant->user_id, // Use user_id from tenant
                'room_id' => $room->id,
                'bill_type' => 'room',
                'description' => 'Monthly rent for ' . Carbon::now()->subMonth()->format('F Y'),
                'bill_date' => Carbon::now()->subMonth()->startOfMonth(),
                'room_rate' => 5000.00,
                'electricity' => 0.00,
                'water' => 0.00,
                'other_charges' => 0.00,
                'total_amount' => 5000.00,
                'amount_paid' => 0.00,
                'due_date' => Carbon::now()->subDays(rand(5, 30)), // 5-30 days overdue
                'status' => 'unpaid',
                'created_by' => 1
            ]);
            $billsCreated++;
            
            // Create overdue utility bill
            Bill::create([
                'tenant_id' => $tenant->user_id, // Use user_id from tenant
                'room_id' => $room->id,
                'bill_type' => 'utility',
                'description' => 'Electricity and water for ' . Carbon::now()->subMonth()->format('F Y'),
                'bill_date' => Carbon::now()->subMonth()->startOfMonth(),
                'room_rate' => 0.00,
                'electricity' => 800.00,
                'water' => 700.00,
                'other_charges' => 0.00,
                'total_amount' => 1500.00,
                'amount_paid' => 0.00,
                'due_date' => Carbon::now()->subDays(rand(10, 45)), // 10-45 days overdue
                'status' => 'unpaid',
                'created_by' => 1
            ]);
            $billsCreated++;
        }

        $this->info("Created {$billsCreated} test overdue bills.");
        $this->line('');
        $this->info('You can now test the penalty system with these commands:');
        $this->line('php artisan penalties:process --dry-run');
        $this->line('php artisan penalties:process');

        return 0;
    }
}
