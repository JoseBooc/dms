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
        // Add indexes to bills table for faster queries
        Schema::table('bills', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'idx_bills_tenant_status');
            $table->index('bill_date', 'idx_bills_bill_date');
            $table->index('due_date', 'idx_bills_due_date');
            $table->index('status', 'idx_bills_status');
        });

        // Add indexes to maintenance_requests table
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'idx_maintenance_tenant_status');
            $table->index(['assigned_to', 'status'], 'idx_maintenance_assigned_status');
            $table->index('status', 'idx_maintenance_status');
            $table->index('priority', 'idx_maintenance_priority');
        });

        // Add indexes to complaints table
        Schema::table('complaints', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'idx_complaints_tenant_status');
            $table->index('status', 'idx_complaints_status');
            $table->index('priority', 'idx_complaints_priority');
            $table->index(['assigned_to', 'status'], 'idx_complaints_assigned_status');
        });

        // Add indexes to room_assignments table
        Schema::table('room_assignments', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'idx_assignments_tenant_status');
            $table->index(['room_id', 'status'], 'idx_assignments_room_status');
            $table->index('status', 'idx_assignments_status');
            $table->index(['start_date', 'end_date'], 'idx_assignments_dates');
        });

        // Add indexes to rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $table->index('status', 'idx_rooms_status');
            $table->index('type', 'idx_rooms_type');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'idx_users_role');
            $table->index('status', 'idx_users_status');
        });

        // Add indexes to deposits table
        Schema::table('deposits', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'idx_deposits_tenant_status');
            $table->index('room_assignment_id', 'idx_deposits_assignment');
            $table->index('status', 'idx_deposits_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes from bills table
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('idx_bills_tenant_status');
            $table->dropIndex('idx_bills_bill_date');
            $table->dropIndex('idx_bills_due_date');
            $table->dropIndex('idx_bills_status');
        });

        // Drop indexes from maintenance_requests table
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropIndex('idx_maintenance_tenant_status');
            $table->dropIndex('idx_maintenance_assigned_status');
            $table->dropIndex('idx_maintenance_status');
            $table->dropIndex('idx_maintenance_priority');
        });

        // Drop indexes from complaints table
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex('idx_complaints_tenant_status');
            $table->dropIndex('idx_complaints_status');
            $table->dropIndex('idx_complaints_priority');
            $table->dropIndex('idx_complaints_assigned_status');
        });

        // Drop indexes from room_assignments table
        Schema::table('room_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_tenant_status');
            $table->dropIndex('idx_assignments_room_status');
            $table->dropIndex('idx_assignments_status');
            $table->dropIndex('idx_assignments_dates');
        });

        // Drop indexes from rooms table
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('idx_rooms_status');
            $table->dropIndex('idx_rooms_type');
        });

        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_status');
        });

        // Drop indexes from deposits table
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex('idx_deposits_tenant_status');
            $table->dropIndex('idx_deposits_assignment');
            $table->dropIndex('idx_deposits_status');
        });
    }
};
