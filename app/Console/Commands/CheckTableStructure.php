<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckTableStructure extends Command
{
    protected $signature = 'check:table {table}';
    protected $description = 'Check table structure';

    public function handle()
    {
        $table = $this->argument('table');
        $columns = Schema::getColumnListing($table);
        
        $this->info("Columns in {$table} table:");
        foreach ($columns as $column) {
            $this->line("- {$column}");
        }
    }
}