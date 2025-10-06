#!/usr/bin/env php
<?php

/**
 * Dashboard Setup Verification Script
 * 
 * This script verifies that the Filament dashboard and widgets are properly configured
 * for team members after cloning the repository.
 */

require __DIR__ . '/vendor/autoload.php';

echo "\n🔍 Verifying Dormitory Management System Setup...\n";
echo str_repeat('=', 60) . "\n";

// Check if required widget files exist
$widgetFiles = [
    'app/Filament/Widgets/RoomOccupancyWidget.php',
    'app/Filament/Widgets/MonthlyRevenueChart.php',
    'app/Filament/Widgets/DashboardOverview.php'
];

echo "\n📁 Checking Widget Files:\n";
foreach ($widgetFiles as $file) {
    if (file_exists($file)) {
        echo "  ✅ {$file}\n";
    } else {
        echo "  ❌ {$file} - MISSING\n";
    }
}

// Check if .env file exists
echo "\n⚙️  Checking Configuration:\n";
if (file_exists('.env')) {
    echo "  ✅ .env file exists\n";
} else {
    echo "  ❌ .env file missing - Run: cp .env.example .env\n";
}

// Check if vendor directory exists
if (is_dir('vendor')) {
    echo "  ✅ Composer dependencies installed\n";
} else {
    echo "  ❌ Composer dependencies missing - Run: composer install\n";
}

// Check if node_modules exists
if (is_dir('node_modules')) {
    echo "  ✅ Node.js dependencies installed\n";
} else {
    echo "  ❌ Node.js dependencies missing - Run: npm install\n";
}

echo "\n🎯 Next Steps:\n";
echo "  1. Ensure database is configured in .env\n";
echo "  2. Run: php artisan migrate\n";
echo "  3. Run: php artisan make:filament-user (create admin user)\n";
echo "  4. Run: php artisan serve\n";
echo "  5. Visit: http://localhost:8000/dashboard\n";

echo "\n📊 Expected Dashboard Features:\n";
echo "  • Statistics cards showing room/tenant data\n";
echo "  • Monthly revenue trend line chart\n";
echo "  • Maintenance & complaints status pie chart\n";
echo "  • Navigation to all management resources\n";

echo "\n" . str_repeat('=', 60) . "\n";
echo "✨ If all checks pass, your dashboard should be ready!\n\n";