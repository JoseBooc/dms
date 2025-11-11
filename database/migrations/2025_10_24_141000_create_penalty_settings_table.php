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
        Schema::create('penalty_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'late_payment_penalty'
            $table->string('description');
            $table->enum('type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('value', 10, 2); // Amount in PHP or percentage
            $table->integer('grace_period_days')->default(0); // Days after due date before penalty applies
            $table->integer('max_penalty_days')->nullable(); // Maximum days to accumulate penalty
            $table->decimal('max_penalty_amount', 10, 2)->nullable(); // Maximum penalty amount
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default penalty settings only if none exist
        if (DB::table('penalty_settings')->where('name', 'late_payment_penalty')->doesntExist()) {
            DB::table('penalty_settings')->insert([
                'name' => 'late_payment_penalty',
                'description' => 'Late Payment Penalty',
                'type' => 'percentage',
                'value' => 5.00, // 5% penalty
                'grace_period_days' => 3, // 3 days grace period
                'max_penalty_days' => 30, // Maximum 30 days of penalty
                'max_penalty_amount' => 1000.00, // Maximum ₱1,000 penalty
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('penalty_settings');
    }
};
