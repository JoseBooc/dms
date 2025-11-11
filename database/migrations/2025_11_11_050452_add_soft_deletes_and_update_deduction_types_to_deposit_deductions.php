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
        // Add soft deletes column
        Schema::table('deposit_deductions', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Update deduction_type enum to Philippine dormitory context
        DB::statement("ALTER TABLE deposit_deductions MODIFY COLUMN deduction_type ENUM('unpaid_rent', 'unpaid_electricity', 'unpaid_water', 'penalty', 'damage') NOT NULL");
        
        // Migrate old data to new types
        DB::table('deposit_deductions')->where('deduction_type', 'damage_charge')->update(['deduction_type' => 'damage']);
        DB::table('deposit_deductions')->where('deduction_type', 'utility_arrears')->update(['deduction_type' => 'unpaid_electricity']);
        DB::table('deposit_deductions')->where('deduction_type', 'cleaning_fee')->update(['deduction_type' => 'damage']);
        DB::table('deposit_deductions')->where('deduction_type', 'other')->update(['deduction_type' => 'damage']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove soft deletes
        Schema::table('deposit_deductions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Revert enum to old values
        DB::statement("ALTER TABLE deposit_deductions MODIFY COLUMN deduction_type ENUM('unpaid_rent', 'damage_charge', 'cleaning_fee', 'utility_arrears', 'other') NOT NULL");
    }
};
