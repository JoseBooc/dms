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
        // Add the new school column
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('school')->nullable()->after('occupation');
        });
        
        // Copy data from university to school
        DB::statement('UPDATE tenants SET school = university');
        
        // Drop the old university column
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('university');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add the university column back
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('university')->nullable()->after('occupation');
        });
        
        // Copy data from school to university
        DB::statement('UPDATE tenants SET university = school');
        
        // Drop the school column
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('school');
        });
    }
};
