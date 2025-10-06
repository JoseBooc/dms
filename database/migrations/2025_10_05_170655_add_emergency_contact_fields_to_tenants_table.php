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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('emergency_contact_first_name')->nullable()->after('current_address');
            $table->string('emergency_contact_middle_name')->nullable()->after('emergency_contact_first_name');
            $table->string('emergency_contact_last_name')->nullable()->after('emergency_contact_middle_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_last_name');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_relationship');
            $table->string('emergency_contact_alternative_phone')->nullable()->after('emergency_contact_phone');
            $table->text('emergency_contact_address')->nullable()->after('emergency_contact_alternative_phone');
            $table->string('emergency_contact_email')->nullable()->after('emergency_contact_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'emergency_contact_first_name',
                'emergency_contact_middle_name',
                'emergency_contact_last_name',
                'emergency_contact_relationship',
                'emergency_contact_phone',
                'emergency_contact_alternative_phone',
                'emergency_contact_address',
                'emergency_contact_email'
            ]);
        });
    }
};
