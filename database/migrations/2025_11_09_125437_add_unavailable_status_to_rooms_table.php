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
        // MySQL doesn't support ALTER ENUM directly, so we need to use raw SQL
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'reserved', 'occupied', 'maintenance', 'unavailable') DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'reserved', 'occupied', 'maintenance') DEFAULT 'available'");
    }
};
