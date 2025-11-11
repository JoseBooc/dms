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
        Schema::table('bills', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'bills_tenant_status_idx');
            $table->index(['room_id', 'due_date'], 'bills_room_due_date_idx');
        });

        Schema::table('utility_readings', function (Blueprint $table) {
            $table->index(['room_id', 'utility_type_id', 'reading_date'], 'utility_room_type_date_idx');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'deposits_tenant_status_idx');
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->index(['tenant_id', 'created_at'], 'fin_trans_tenant_created_idx');
        });

        Schema::table('room_assignments', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'assignments_tenant_status_idx');
            $table->index(['room_id', 'status'], 'assignments_room_status_idx');
        });
    }

    public function down()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('bills_tenant_status_idx');
            $table->dropIndex('bills_room_due_date_idx');
        });

        Schema::table('utility_readings', function (Blueprint $table) {
            $table->dropIndex('utility_room_type_date_idx');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex('deposits_tenant_status_idx');
        });

        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropIndex('fin_trans_tenant_created_idx');
        });

        Schema::table('room_assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_tenant_status_idx');
            $table->dropIndex('assignments_room_status_idx');
        });
    }
};
