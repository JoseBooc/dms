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
            // Add composite index for faster bill-reading linking queries
            $table->index(['tenant_id', 'room_id', 'status', 'bill_id'], 'idx_utility_readings_billing');
            
            // Add index on bill_id for relationship queries
            $table->index('bill_id', 'idx_utility_readings_bill_id');
            
            // Add index on status for filtering
            $table->index('status', 'idx_utility_readings_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->dropIndex('idx_utility_readings_billing');
            $table->dropIndex('idx_utility_readings_bill_id');
            $table->dropIndex('idx_utility_readings_status');
        });
    }
};
