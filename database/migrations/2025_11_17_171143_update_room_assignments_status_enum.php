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
        // Update the enum to include inactive and terminated instead of completed
        DB::statement("ALTER TABLE room_assignments MODIFY COLUMN status ENUM('pending', 'active', 'inactive', 'terminated') NOT NULL DEFAULT 'pending'");
        
        // Update any existing 'completed' records to 'terminated' 
        DB::table('room_assignments')
            ->where('status', 'completed')
            ->update(['status' => 'terminated']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE room_assignments MODIFY COLUMN status ENUM('active', 'pending', 'completed') NOT NULL DEFAULT 'pending'");
        
        // Convert back any terminated/inactive records
        DB::table('room_assignments')
            ->whereIn('status', ['terminated', 'inactive'])
            ->update(['status' => 'completed']);
    }
};
