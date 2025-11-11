<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            // First, make utility_type_id nullable
            $table->unsignedBigInteger('utility_type_id')->nullable()->change();
        });
        
        // Note: The unique constraint 'unique_reading_per_utility_room_date' remains
        // It will allow NULL values for utility_type_id in the unified approach
        // This means we can have one reading per room/date with NULL utility_type_id
        // (the unified reading) and still maintain data integrity
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            // Revert back to non-nullable
            $table->unsignedBigInteger('utility_type_id')->nullable(false)->change();
        });
    }
};
