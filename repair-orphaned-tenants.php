<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Tenant;

echo "=== REPAIRING ORPHANED TENANT USERS ===\n\n";

$orphanedUsers = User::where('role', 'tenant')
    ->doesntHave('tenant')
    ->get();

if ($orphanedUsers->count() === 0) {
    echo "No orphaned users found. All tenant users have tenant records.\n";
    exit(0);
}

echo "Found {$orphanedUsers->count()} orphaned tenant users.\n\n";

foreach ($orphanedUsers as $user) {
    echo "Processing User ID {$user->id}: {$user->name}...\n";
    
    try {
        // Create tenant record from user data
        $tenant = Tenant::create([
            'user_id' => $user->id,
            'first_name' => $user->first_name ?? explode(' ', $user->name)[0],
            'middle_name' => $user->middle_name ?? null,
            'last_name' => $user->last_name ?? (explode(' ', $user->name)[1] ?? 'N/A'),
            'birth_date' => $user->birth_date ?? now()->subYears(20),
            'gender' => $user->gender ?? 'female',
            'nationality' => 'Filipino',
            'civil_status' => 'single',
            'school' => 'N/A',
            'course' => 'N/A',
            'phone_number' => '0000000000',
            'personal_email' => $user->email,
            'permanent_address' => 'N/A',
            'emergency_contact_first_name' => 'N/A',
            'emergency_contact_last_name' => 'N/A',
            'emergency_contact_relationship' => 'N/A',
            'emergency_contact_phone' => '0000000000',
            'id_type' => 'student_id',
            'id_number' => 'N/A',
            'remarks' => null,
        ]);
        
        echo "  ✓ Created Tenant record ID: {$tenant->id}\n";
    } catch (\Exception $e) {
        echo "  ✗ Error: {$e->getMessage()}\n";
    }
}

echo "\n=== REPAIR COMPLETE ===\n";
echo "Please update the tenant records with correct information through the admin panel.\n";
