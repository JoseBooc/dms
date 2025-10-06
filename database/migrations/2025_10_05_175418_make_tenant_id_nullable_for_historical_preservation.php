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
        // Make tenant_id nullable in maintenance_requests
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->change();
        });

        // Make tenant_id nullable in complaints  
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->change();
        });

        // Make tenant_id nullable in bills
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert tenant_id to not nullable (be careful with existing null data)
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable(false)->change();
        });
    }
};
