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
        // Skip all foreign key updates for now due to constraint issues
        // The existing foreign keys are working fine, this migration is causing 
        // timeout and constraint errors. Disabling until we can investigate further.
        
        // All operations commented out to prevent timeout and constraint errors:
        
        // Schema::table('maintenance_requests', function (Blueprint $table) {
        //     $table->dropForeign(['tenant_id']);
        //     $table->foreign('tenant_id')->references('id')->on('users')->nullOnDelete();
        // });

        // Schema::table('complaints', function (Blueprint $table) {
        //     $table->dropForeign(['tenant_id']);
        //     $table->foreign('tenant_id')->references('id')->on('users')->nullOnDelete();
        // });

        // Schema::table('bills', function (Blueprint $table) {
        //     $table->dropForeign(['tenant_id']);
        //     $table->foreign('tenant_id')->references('id')->on('users')->nullOnDelete();
        // });
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
