<?php

use App\Models\Bill;
use App\Models\Tenant;
use App\Models\Room;
use Carbon\Carbon;

// Create some test overdue bills
echo "Creating test overdue bills...\n";

// Get some tenants and rooms
$tenants = Tenant::take(3)->get();
$rooms = Room::take(3)->get();

if ($tenants->isEmpty() || $rooms->isEmpty()) {
    echo "No tenants or rooms found. Please create some test data first.\n";
    exit;
}

foreach ($tenants as $index => $tenant) {
    $room = $rooms[$index] ?? $rooms->first();
    
    // Create overdue rent bill
    Bill::create([
        'tenant_id' => $tenant->id,
        'room_id' => $room->id,
        'bill_type' => 'rent',
        'description' => 'Monthly rent for ' . Carbon::now()->subMonth()->format('F Y'),
        'total_amount' => 5000.00,
        'amount_paid' => 0.00,
        'due_date' => Carbon::now()->subDays(rand(5, 30)), // 5-30 days overdue
        'status' => 'unpaid',
        'created_by' => 1
    ]);
    
    // Create overdue utility bill
    Bill::create([
        'tenant_id' => $tenant->id,
        'room_id' => $room->id,
        'bill_type' => 'utility',
        'description' => 'Electricity and water for ' . Carbon::now()->subMonth()->format('F Y'),
        'total_amount' => 1500.00,
        'amount_paid' => 0.00,
        'due_date' => Carbon::now()->subDays(rand(10, 45)), // 10-45 days overdue
        'status' => 'unpaid',
        'created_by' => 1
    ]);
}

echo "Created " . ($tenants->count() * 2) . " test overdue bills.\n";
echo "You can now test the penalty system!\n";
echo "\nCommands to try:\n";
echo "php artisan penalties:process --dry-run\n";
echo "php artisan penalties:process\n";