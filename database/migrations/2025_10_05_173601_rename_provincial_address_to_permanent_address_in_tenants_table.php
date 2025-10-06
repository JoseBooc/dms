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
        // Add the new permanent_address column
        Schema::table('tenants', function (Blueprint $table) {
            $table->text('permanent_address')->nullable()->after('course');
        });
        
        // Copy data from provincial_address to permanent_address
        DB::statement('UPDATE tenants SET permanent_address = provincial_address');
        
        // Drop the old provincial_address column
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('provincial_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add the provincial_address column back
        Schema::table('tenants', function (Blueprint $table) {
            $table->text('provincial_address')->nullable()->after('course');
        });
        
        // Copy data from permanent_address to provincial_address
        DB::statement('UPDATE tenants SET provincial_address = permanent_address');
        
        // Drop the permanent_address column
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('permanent_address');
        });
    }
};
