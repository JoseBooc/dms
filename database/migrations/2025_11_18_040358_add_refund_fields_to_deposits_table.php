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
        Schema::table('deposits', function (Blueprint $table) {
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('refundable_amount');
            $table->string('refund_method')->nullable()->after('refunded_amount');
            $table->string('reference_number')->nullable()->after('refund_method');
            $table->text('refund_notes')->nullable()->after('reference_number');
            $table->timestamp('refunded_at')->nullable()->after('refund_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'refunded_amount',
                'refund_method', 
                'reference_number',
                'refund_notes',
                'refunded_at'
            ]);
        });
    }
};
