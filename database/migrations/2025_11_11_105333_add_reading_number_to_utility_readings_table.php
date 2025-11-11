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
        Schema::table('utility_readings', function (Blueprint $table) {
            // Add reading_number column - unique sequential identifier
            $table->string('reading_number', 20)->unique()->after('id')->nullable();
            
            // Add index for faster searching
            $table->index('reading_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('utility_readings', function (Blueprint $table) {
            $table->dropIndex(['reading_number']);
            $table->dropColumn('reading_number');
        });
    }
};
