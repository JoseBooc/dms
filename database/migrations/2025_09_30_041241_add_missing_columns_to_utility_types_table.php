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
        Schema::table('utility_types', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('utility_types', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn('utility_types', 'unit')) {
                $table->string('unit')->after('name');
            }
            if (!Schema::hasColumn('utility_types', 'description')) {
                $table->text('description')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('utility_types', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('utility_types', function (Blueprint $table) {
            $table->dropColumn(['name', 'unit', 'description', 'status']);
        });
    }
};
