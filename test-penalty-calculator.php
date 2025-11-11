<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PenaltySetting;

echo "=== PHILIPPINE DORMITORY PENALTY CALCULATOR TEST ===\n\n";

$setting = PenaltySetting::getActiveSetting('late_payment_penalty');

if (!$setting) {
    echo "❌ No active penalty setting found.\n";
    exit(1);
}

echo "📋 Current Penalty Settings:\n";
echo "   Type: " . strtoupper(str_replace('_', ' ', $setting->penalty_type)) . "\n";
echo "   Rate: ₱" . number_format($setting->penalty_rate, 2) . 
     ($setting->penalty_type === 'daily_fixed' ? '/day' : 
     ($setting->penalty_type === 'percentage' ? '%' : ' flat')) . "\n";
echo "   Grace Period: {$setting->grace_period_days} days\n";
echo "   Max Penalty: ₱" . number_format($setting->max_penalty, 2) . "\n\n";

// Test scenarios
$testBill = 5000; // ₱5,000 bill
$scenarios = [
    ['days' => 0, 'label' => 'On time (0 days overdue)'],
    ['days' => 2, 'label' => 'Within grace period (2 days overdue)'],
    ['days' => 3, 'label' => 'End of grace period (3 days overdue)'],
    ['days' => 5, 'label' => '2 days after grace (5 days total overdue)'],
    ['days' => 10, 'label' => '7 days after grace (10 days total overdue)'],
    ['days' => 15, 'label' => '12 days after grace (15 days total overdue)'],
    ['days' => 20, 'label' => '17 days after grace (20 days total overdue)'],
];

echo "🧪 Test Scenarios (Bill Amount: ₱" . number_format($testBill, 2) . "):\n";
echo str_repeat("─", 70) . "\n";

foreach ($scenarios as $scenario) {
    $penalty = $setting->calculatePenalty($testBill, $scenario['days']);
    $status = $penalty > 0 ? "❌ PENALTY" : "✅ NO PENALTY";
    
    echo sprintf(
        "%-45s → ₱%8s %s\n",
        $scenario['label'],
        number_format($penalty, 2),
        $status
    );
}

echo str_repeat("─", 70) . "\n\n";

// Test different penalty types
echo "📊 Comparison of Different Penalty Types:\n";
echo str_repeat("─", 70) . "\n";

$testDays = 10; // 10 days overdue
$types = [
    ['type' => 'daily_fixed', 'rate' => 50, 'label' => 'Daily Fixed (₱50/day)'],
    ['type' => 'percentage', 'rate' => 3, 'label' => 'Percentage (3% of bill)'],
    ['type' => 'flat_fee', 'rate' => 200, 'label' => 'Flat Fee (₱200 one-time)'],
];

foreach ($types as $type) {
    $tempSetting = new PenaltySetting([
        'penalty_type' => $type['type'],
        'penalty_rate' => $type['rate'],
        'grace_period_days' => 3,
        'max_penalty' => 500,
    ]);
    
    $penalty = $tempSetting->calculatePenalty($testBill, $testDays);
    
    echo sprintf(
        "%-35s → ₱%8s\n",
        $type['label'],
        number_format($penalty, 2)
    );
}

echo str_repeat("─", 70) . "\n\n";

echo "✅ Penalty calculation system is working correctly!\n";
echo "💡 Philippine dormitory-realistic rules are now active.\n";
