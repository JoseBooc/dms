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
        // For maintenance_requests table - add foreign key with nullOnDelete
        // Note: maintenance_requests.tenant_id references tenants table
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });

        // Update complaints table to set null on tenant deletion  
        // Note: complaints.tenant_id references users table (tenant_id is actually user_id)
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->foreign('tenant_id')->references('id')->on('users')->nullOnDelete();
        });

        // Update bills table to set null on tenant deletion
        // Note: bills.tenant_id references users table (tenant_id is actually user_id)
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->foreign('tenant_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove foreign keys created in up()
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->foreign('tenant_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->foreign('tenant_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
