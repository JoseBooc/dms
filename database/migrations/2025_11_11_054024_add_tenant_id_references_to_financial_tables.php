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
        // Add tenant_id to bills table
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('tenant_id_new')->nullable()->after('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->index('tenant_id_new');
        });

        // Backfill tenant_id_new from user_id via tenants table
        DB::statement('
            UPDATE bills 
            SET tenant_id_new = (
                SELECT id FROM tenants WHERE tenants.user_id = bills.tenant_id
            )
            WHERE tenant_id IS NOT NULL
        ');

        // Add tenant_id to deposits table
        Schema::table('deposits', function (Blueprint $table) {
            $table->foreignId('tenant_id_new')->nullable()->after('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->index('tenant_id_new');
        });

        // Backfill deposits
        DB::statement('
            UPDATE deposits 
            SET tenant_id_new = (
                SELECT id FROM tenants WHERE tenants.user_id = deposits.tenant_id
            )
            WHERE tenant_id IS NOT NULL
        ');

        // Add tenant_id to complaints table
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('tenant_id_new')->nullable()->after('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->index('tenant_id_new');
        });

        // Backfill complaints
        DB::statement('
            UPDATE complaints 
            SET tenant_id_new = (
                SELECT id FROM tenants WHERE tenants.user_id = complaints.tenant_id
            )
            WHERE tenant_id IS NOT NULL
        ');

        // Add tenant_id to maintenance_requests table
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignId('tenant_id_new')->nullable()->after('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->index('tenant_id_new');
        });

        // Backfill maintenance_requests
        DB::statement('
            UPDATE maintenance_requests 
            SET tenant_id_new = (
                SELECT id FROM tenants WHERE tenants.user_id = maintenance_requests.tenant_id
            )
            WHERE tenant_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['tenant_id_new']);
            $table->dropIndex(['tenant_id_new']);
            $table->dropColumn('tenant_id_new');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->dropForeign(['tenant_id_new']);
            $table->dropIndex(['tenant_id_new']);
            $table->dropColumn('tenant_id_new');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['tenant_id_new']);
            $table->dropIndex(['tenant_id_new']);
            $table->dropColumn('tenant_id_new');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropForeign(['tenant_id_new']);
            $table->dropIndex(['tenant_id_new']);
            $table->dropColumn('tenant_id_new');
        });
    }
};
