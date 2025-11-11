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
        Schema::table('penalty_settings', function (Blueprint $table) {
            // Rename columns to match new Philippine dormitory rules
            $table->renameColumn('type', 'penalty_type');
            $table->renameColumn('value', 'penalty_rate');
            $table->renameColumn('max_penalty_amount', 'max_penalty');
            
            // Drop unused column
            if (Schema::hasColumn('penalty_settings', 'max_penalty_days')) {
                $table->dropColumn('max_penalty_days');
            }
        });
        
        // Rename is_active to active separately and ensure it has a default
        Schema::table('penalty_settings', function (Blueprint $table) {
            $table->renameColumn('is_active', 'active');
        });
        
        // Ensure active column has default value
        DB::statement('ALTER TABLE penalty_settings MODIFY active TINYINT(1) NOT NULL DEFAULT 1');
        
        // Update existing data to match new penalty types
        DB::table('penalty_settings')->where('penalty_type', 'fixed')->update(['penalty_type' => 'daily_fixed']);
        DB::table('penalty_settings')->where('penalty_type', 'percentage')->update(['penalty_type' => 'percentage']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penalty_settings', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('penalty_type', 'type');
            $table->renameColumn('penalty_rate', 'value');
            $table->renameColumn('active', 'is_active');
            $table->renameColumn('max_penalty', 'max_penalty_amount');
            
            // Add back the dropped column
            $table->integer('max_penalty_days')->nullable()->after('grace_period_days');
        });
        
        // Revert penalty type changes
        DB::table('penalty_settings')->where('type', 'daily_fixed')->update(['type' => 'fixed']);
    }
};
