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
        // MySQL doesn't support ALTER ENUM directly, so we use raw SQL
        DB::statement("ALTER TABLE bills MODIFY COLUMN bill_type ENUM('room', 'utility', 'maintenance', 'penalty', 'other') DEFAULT 'room'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove penalty from enum
        DB::statement("ALTER TABLE bills MODIFY COLUMN bill_type ENUM('room', 'utility', 'maintenance', 'other') DEFAULT 'room'");
    }
};
