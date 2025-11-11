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
        // Add check constraints to ensure non-negative amounts
        // Note: MySQL doesn't support CHECK constraints until 8.0.16
        // For older versions, this will be enforced at application level
        
        DB::statement('ALTER TABLE deposits MODIFY COLUMN amount DECIMAL(10,2) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE deposits MODIFY COLUMN deductions_total DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE deposits MODIFY COLUMN refundable_amount DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0');
        
        // Add comment to document the business logic
        DB::statement('ALTER TABLE deposits COMMENT = "Refundable Amount = MAX(0, Amount - Deductions Total)"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to signed decimals
        DB::statement('ALTER TABLE deposits MODIFY COLUMN amount DECIMAL(10,2) NOT NULL');
        DB::statement('ALTER TABLE deposits MODIFY COLUMN deductions_total DECIMAL(10,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE deposits MODIFY COLUMN refundable_amount DECIMAL(10,2) NOT NULL DEFAULT 0');
        
        DB::statement('ALTER TABLE deposits COMMENT = ""');
    }
};
