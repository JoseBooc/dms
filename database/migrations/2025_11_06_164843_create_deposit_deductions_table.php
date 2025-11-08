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
        Schema::create('deposit_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deposit_id')->constrained()->onDelete('cascade');
            $table->foreignId('bill_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('deduction_type', ['unpaid_rent', 'damage_charge', 'cleaning_fee', 'utility_arrears', 'other']);
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->text('details')->nullable();
            $table->date('deduction_date');
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_deductions');
    }
};
