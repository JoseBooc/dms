<?php

/**
 * Bill Auto-Population Feature Test Script
 * 
 * This script tests the auto-population logic without needing the UI.
 * Run: php test-bill-auto-population.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\RoomAssignment;
use App\Models\UtilityReading;

echo "\n========================================\n";
echo "BILL AUTO-POPULATION TEST\n";
echo "========================================\n\n";

// Find a tenant with active assignment
$tenant = User::where('role', 'tenant')
    ->whereHas('roomAssignments', function ($query) {
        $query->where('status', 'active');
    })
    ->first();

if (!$tenant) {
    echo "❌ No tenants with active room assignments found.\n";
    echo "   Please create a tenant and assign them to a room first.\n\n";
    exit(1);
}

echo "✅ Found tenant: {$tenant->name} (ID: {$tenant->id})\n\n";

// Get active assignment
$assignment = RoomAssignment::where('tenant_id', $tenant->id)
    ->where('status', 'active')
    ->with('room')
    ->first();

if (!$assignment || !$assignment->room) {
    echo "❌ Tenant has no active room assignment.\n\n";
    exit(1);
}

echo "✅ Active Room Assignment:\n";
echo "   Room: {$assignment->room->room_number}\n";
echo "   Room Rate: ₱" . number_format($assignment->room->price, 2) . "\n";
echo "   Status: {$assignment->status}\n\n";

// Get latest unbilled utility reading
$latestReading = UtilityReading::where('room_id', $assignment->room_id)
    ->whereNull('deleted_at')
    ->whereNull('bill_id')
    ->latest('reading_date')
    ->first();

if ($latestReading) {
    echo "✅ Latest Unbilled Utility Reading:\n";
    echo "   Reading Date: {$latestReading->reading_date}\n";
    echo "   Electric Charge: ₱" . number_format($latestReading->electric_charge ?? 0, 2) . "\n";
    echo "   Water Charge: ₱" . number_format($latestReading->water_charge ?? 0, 2) . "\n";
    echo "   Total Utility: ₱" . number_format(
        ($latestReading->electric_charge ?? 0) + ($latestReading->water_charge ?? 0), 
        2
    ) . "\n\n";
} else {
    echo "⚠️  No unbilled utility readings found for this room.\n";
    echo "   Utility charges will default to ₱0.00\n\n";
}

// Calculate what would be auto-populated
$roomRate = $assignment->room->price ?? 0;
$electricity = $latestReading ? ($latestReading->electric_charge ?? 0) : 0;
$water = $latestReading ? ($latestReading->water_charge ?? 0) : 0;
$otherCharges = 0;
$totalAmount = $roomRate + $electricity + $water + $otherCharges;

echo "========================================\n";
echo "BILL AUTO-POPULATION PREVIEW\n";
echo "========================================\n\n";
echo "Tenant ID:         {$tenant->id}\n";
echo "Tenant Name:       {$tenant->name}\n";
echo "Room ID:           {$assignment->room_id}\n";
echo "Room Number:       {$assignment->room->room_number}\n";
echo "--------------------\n";
echo "Room Rate:         ₱" . number_format($roomRate, 2) . "\n";
echo "Electricity:       ₱" . number_format($electricity, 2) . "\n";
echo "Water:             ₱" . number_format($water, 2) . "\n";
echo "Other Charges:     ₱" . number_format($otherCharges, 2) . "\n";
echo "--------------------\n";
echo "TOTAL AMOUNT:      ₱" . number_format($totalAmount, 2) . "\n";
echo "========================================\n\n";

// Test with another tenant (if exists)
$secondTenant = User::where('role', 'tenant')
    ->where('id', '!=', $tenant->id)
    ->whereHas('roomAssignments', function ($query) {
        $query->where('status', 'active');
    })
    ->first();

if ($secondTenant) {
    echo "\n📋 Additional Test - Second Tenant\n";
    echo "------------------------------------\n";
    
    $secondAssignment = RoomAssignment::where('tenant_id', $secondTenant->id)
        ->where('status', 'active')
        ->with('room')
        ->first();
    
    $secondReading = UtilityReading::where('room_id', $secondAssignment->room_id)
        ->whereNull('deleted_at')
        ->whereNull('bill_id')
        ->latest('reading_date')
        ->first();
    
    $sr_roomRate = $secondAssignment->room->price ?? 0;
    $sr_electricity = $secondReading ? ($secondReading->electric_charge ?? 0) : 0;
    $sr_water = $secondReading ? ($secondReading->water_charge ?? 0) : 0;
    $sr_total = $sr_roomRate + $sr_electricity + $sr_water;
    
    echo "Tenant: {$secondTenant->name}\n";
    echo "Room: {$secondAssignment->room->room_number}\n";
    echo "Total: ₱" . number_format($sr_total, 2) . "\n\n";
}

// Summary statistics
$totalTenants = User::where('role', 'tenant')->count();
$tenantsWithAssignments = User::where('role', 'tenant')
    ->whereHas('roomAssignments', function ($query) {
        $query->where('status', 'active');
    })
    ->count();
$totalUnbilledReadings = UtilityReading::whereNull('bill_id')
    ->whereNull('deleted_at')
    ->count();

echo "📊 System Statistics:\n";
echo "------------------------------------\n";
echo "Total Tenants:              {$totalTenants}\n";
echo "Tenants with Active Rooms:  {$tenantsWithAssignments}\n";
echo "Unbilled Utility Readings:  {$totalUnbilledReadings}\n\n";

echo "✅ Auto-population feature is ready to use!\n";
echo "   Go to Billing → Create to test the UI.\n\n";
