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
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('penalty_amount', 10, 2)->default(0)->after('amount_paid');
            $table->date('penalty_applied_date')->nullable()->after('penalty_amount');
            $table->integer('overdue_days')->default(0)->after('penalty_applied_date');
            $table->boolean('penalty_waived')->default(false)->after('overdue_days');
            $table->text('penalty_waiver_reason')->nullable()->after('penalty_waived');
            $table->foreignId('penalty_waived_by')->nullable()->constrained('users')->after('penalty_waiver_reason');
            $table->timestamp('penalty_waived_at')->nullable()->after('penalty_waived_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['penalty_waived_by']);
            $table->dropColumn([
                'penalty_amount',
                'penalty_applied_date', 
                'overdue_days',
                'penalty_waived',
                'penalty_waiver_reason',
                'penalty_waived_by',
                'penalty_waived_at'
            ]);
        });
    }
};
