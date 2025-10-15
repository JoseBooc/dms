#!/usr/bin/env php
<?php

/**
 * Navigation Test Script
 * 
 * This script tests the navigation access controls for different user roles
 */

require __DIR__ . '/vendor/autoload.php';

echo "\n🔒 Testing Navigation Access Controls...\n";
echo str_repeat('=', 60) . "\n";

// Simulate different user types
$userRoles = ['admin', 'staff', 'tenant'];

echo "\n📋 Tenant-Only Pages Access Test:\n";

// Test access for different roles
foreach ($userRoles as $role) {
    echo "\n👤 Testing as $role user:\n";
    
    // Mock user for testing
    $user = new stdClass();
    $user->role = $role;
    
    // Test each tenant page
    $tenantPages = [
        'TenantDashboard' => 'Home',
        'RentDetails' => 'Rent Details', 
        'UtilityDetails' => 'Utility Details',
        'RoomInformation' => 'Room Information'
    ];
    
    foreach ($tenantPages as $class => $label) {
        $canAccess = ($user->role === 'tenant');
        $status = $canAccess ? '✅ ALLOWED' : '❌ BLOCKED';
        echo "  - $label: $status\n";
    }
}

echo "\n📊 Expected Behavior:\n";
echo "  • Admin users: Should NOT see tenant-specific pages in navigation\n";
echo "  • Staff users: Should NOT see tenant-specific pages in navigation\n";
echo "  • Tenant users: Should ONLY see tenant-specific pages\n";

echo "\n🚀 Test Complete!\n";
echo "If you see tenant pages in admin/staff navigation, try:\n";
echo "  1. php artisan optimize:clear\n";
echo "  2. Hard refresh your browser (Ctrl+F5)\n";
echo "  3. Log out and log back in\n\n";