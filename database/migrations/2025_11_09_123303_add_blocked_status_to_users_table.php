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
        // Update the status column comment to include 'blocked' status
        // This migration ensures 'blocked' is a valid status value
        
        // Add comment to document the valid status values
        DB::statement("ALTER TABLE users MODIFY COLUMN status VARCHAR(255) DEFAULT 'active' COMMENT 'Valid values: active, blocked, inactive, suspended'");
        
        // Note: The status column already exists as VARCHAR, so 'blocked' value is already supported
        // This migration is for documentation and future reference
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert comment
        DB::statement("ALTER TABLE users MODIFY COLUMN status VARCHAR(255) DEFAULT 'active' COMMENT ''");
    }
};
