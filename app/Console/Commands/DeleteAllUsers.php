<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteAllUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:delete-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all users from the system with proper cascade handling';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();
        $count = $users->count();

        if ($count === 0) {
            $this->info('No users found in the system.');
            return Command::SUCCESS;
        }

        $this->info("Found {$count} users:");
        $this->newLine();

        foreach ($users as $user) {
            $this->line("  - {$user->name} ({$user->email}) - {$user->role}");
        }

        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete ALL users? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info('Starting user deletion process...');
        $this->newLine();

        DB::beginTransaction();

        try {
            $deleted = 0;
            $errors = 0;

            foreach ($users as $user) {
                try {
                    $this->info("Deleting user: {$user->name} ({$user->email})...");
                    
                    // The User model's boot method handles cascade deletion
                    // of related records like room assignments, tenants, etc.
                    $user->delete();
                    
                    $deleted++;
                    $this->line("  ✓ Deleted successfully");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("  ✗ Error deleting user: " . $e->getMessage());
                }
            }

            DB::commit();

            $this->newLine();
            $this->info("Deletion complete!");
            $this->line("  Deleted: {$deleted} users");
            
            if ($errors > 0) {
                $this->error("  Errors: {$errors} users");
            }

            // Verify deletion
            $remaining = User::count();
            $this->newLine();
            $this->info("Remaining users in system: {$remaining}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Transaction failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
