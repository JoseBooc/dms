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
            // Make legacy fields nullable since we're using unified water/electric fields
            $table->decimal('current_reading', 10, 2)->nullable()->change();
            $table->decimal('previous_reading', 10, 2)->nullable()->change();
            $table->decimal('consumption', 10, 2)->nullable()->change();
            $table->decimal('price', 10, 2)->nullable()->change();
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
            // Revert back to non-nullable with defaults
            $table->decimal('current_reading', 10, 2)->nullable(false)->change();
            $table->decimal('previous_reading', 10, 2)->default(0)->nullable(false)->change();
            $table->decimal('consumption', 10, 2)->default(0)->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable()->change(); // Keep price nullable
        });
    }
};
