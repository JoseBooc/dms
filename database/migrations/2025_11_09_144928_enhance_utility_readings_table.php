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
            // Add separate fields for water readings
            $table->decimal('previous_water_reading', 10, 2)->nullable()->after('consumption');
            $table->decimal('current_water_reading', 10, 2)->nullable()->after('previous_water_reading');
            $table->decimal('water_consumption', 10, 2)->nullable()->after('current_water_reading');
            $table->decimal('water_rate', 10, 2)->nullable()->after('water_consumption')->comment('₱/m³');
            $table->decimal('water_charge', 10, 2)->nullable()->after('water_rate');
            
            // Add separate fields for electric readings
            $table->decimal('previous_electric_reading', 10, 2)->nullable()->after('water_charge');
            $table->decimal('current_electric_reading', 10, 2)->nullable()->after('previous_electric_reading');
            $table->decimal('electric_consumption', 10, 2)->nullable()->after('current_electric_reading');
            $table->decimal('electric_rate', 10, 2)->nullable()->after('electric_consumption')->comment('₱/kWh');
            $table->decimal('electric_charge', 10, 2)->nullable()->after('electric_rate');
            
            // Add billing period field
            $table->string('billing_period')->nullable()->after('reading_date')->comment('e.g., Nov 2025');
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
            $table->dropColumn([
                'previous_water_reading',
                'current_water_reading',
                'water_consumption',
                'water_rate',
                'water_charge',
                'previous_electric_reading',
                'current_electric_reading',
                'electric_consumption',
                'electric_rate',
                'electric_charge',
                'billing_period',
            ]);
        });
    }
};
