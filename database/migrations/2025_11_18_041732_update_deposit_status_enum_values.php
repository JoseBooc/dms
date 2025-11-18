<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, add new enum values while keeping old ones
        DB::statement("ALTER TABLE deposits MODIFY COLUMN status ENUM('active', 'partially_refunded', 'fully_refunded', 'deducted', 'refunded', 'forfeited') NOT NULL DEFAULT 'active'");
        
        // Update existing data
        DB::statement("UPDATE deposits SET status = 'refunded' WHERE status = 'fully_refunded'");
        DB::statement("UPDATE deposits SET status = 'deducted' WHERE status = 'partially_refunded'");
        
        // Remove old enum values, keeping only new ones
        DB::statement("ALTER TABLE deposits MODIFY COLUMN status ENUM('active', 'deducted', 'refunded', 'forfeited') NOT NULL DEFAULT 'active'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert data changes
        DB::statement("UPDATE deposits SET status = 'fully_refunded' WHERE status = 'refunded'");
        DB::statement("UPDATE deposits SET status = 'partially_refunded' WHERE status = 'deducted'");
        
        // Revert enum column to original values
        DB::statement("ALTER TABLE deposits MODIFY COLUMN status ENUM('active', 'partially_refunded', 'fully_refunded', 'forfeited') NOT NULL DEFAULT 'active'");
    }
};
