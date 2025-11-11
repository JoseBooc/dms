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
            // Add status column first
            $table->enum('status', ['pending', 'verified', 'billed'])->default('pending')->after('electric_charge');
            // Add override fields
            $table->text('override_reason')->nullable()->after('status');
            $table->foreignId('override_by')->nullable()->after('override_reason')->constrained('users')->onDelete('set null');
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
            $table->dropForeign(['override_by']);
            $table->dropColumn(['status', 'override_reason', 'override_by']);
        });
    }
};
