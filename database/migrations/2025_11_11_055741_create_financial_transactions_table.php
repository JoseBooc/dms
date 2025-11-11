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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->enum('type', [
                'bill_created',
                'bill_payment',
                'penalty_applied',
                'penalty_waived',
                'deposit_collected',
                'deposit_deduction',
                'deposit_refund',
                'other'
            ]);
            $table->string('reference_type')->nullable(); // Model class name (Bill, Deposit, etc.)
            $table->unsignedBigInteger('reference_id')->nullable(); // Model ID
            $table->decimal('amount', 10, 2); // Transaction amount (positive or negative)
            $table->decimal('running_balance', 10, 2)->nullable(); // Tenant's running balance after this transaction
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('financial_transactions');
    }
};
