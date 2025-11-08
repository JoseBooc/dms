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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('room_assignment_id')->constrained('room_assignments')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('deductions_total', 10, 2)->default(0);
            $table->decimal('refundable_amount', 10, 2)->default(0);
            $table->enum('status', ['active', 'partially_refunded', 'fully_refunded', 'forfeited'])->default('active');
            $table->date('collected_date');
            $table->date('refund_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('collected_by')->constrained('users');
            $table->foreignId('refunded_by')->nullable()->constrained('users');
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
        Schema::dropIfExists('deposits');
    }
};
