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
            // Add nullable foreign key to link penalty bills to their source bill
            $table->foreignId('penalty_bill_for_id')
                ->nullable()
                ->after('room_id')
                ->constrained('bills')
                ->onDelete('set null');
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
            $table->dropForeign(['penalty_bill_for_id']);
            $table->dropColumn('penalty_bill_for_id');
        });
    }
};
