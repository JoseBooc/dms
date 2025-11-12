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
        Schema::table('tenants', function (Blueprint $table) {
            // Add indexes for tenant search optimization
            $table->index('last_name', 'idx_tenants_last_name');
            $table->index('first_name', 'idx_tenants_first_name');
            // Composite index for full name searches
            $table->index(['last_name', 'first_name'], 'idx_tenants_full_name');
        });

        Schema::table('rooms', function (Blueprint $table) {
            // Add indexes for room search and availability checks
            $table->index('room_number', 'idx_rooms_room_number');
            $table->index('is_hidden', 'idx_rooms_is_hidden');
            // Composite index for available rooms query
            $table->index(['status', 'is_hidden', 'current_occupants', 'capacity'], 'idx_rooms_availability');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex('idx_tenants_last_name');
            $table->dropIndex('idx_tenants_first_name');
            $table->dropIndex('idx_tenants_full_name');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('idx_rooms_room_number');
            $table->dropIndex('idx_rooms_is_hidden');
            $table->dropIndex('idx_rooms_availability');
        });
    }
};
