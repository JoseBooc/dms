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
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->boolean('is_common_area')->default(false)->after('area');
            $table->string('before_photo')->nullable()->after('description');
            $table->string('after_photo')->nullable()->after('before_photo');
            $table->text('cancel_reason')->nullable()->after('completion_notes');
            $table->timestamp('completed_at')->nullable()->after('cancel_reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn(['is_common_area', 'before_photo', 'after_photo', 'cancel_reason', 'completed_at']);
        });
    }
};
