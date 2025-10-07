<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateUserRole extends Command
{
    protected $signature = 'user:role {email} {role}';
    protected $description = 'Update user role by email';

    public function handle()
    {
        $email = $this->argument('email');
        $role = $this->argument('role');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }
        
        $user->role = $role;
        $user->save();
        
        $this->info("Updated {$user->name} ({$user->email}) role to: {$role}");
        return 0;
    }
}