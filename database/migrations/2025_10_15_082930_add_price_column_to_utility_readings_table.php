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
            if (!Schema::hasColumn('utility_readings', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('consumption');
            }
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
            if (Schema::hasColumn('utility_readings', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
