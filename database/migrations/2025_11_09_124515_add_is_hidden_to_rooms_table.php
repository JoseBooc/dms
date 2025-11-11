<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rooms', function (Blueprint $table) {
            // Add is_hidden column if it doesn't exist
            if (!Schema::hasColumn('rooms', 'is_hidden')) {
                $table->boolean('is_hidden')->default(false)->after('hidden');
            }
            
            // Migrate data from 'hidden' to 'is_hidden' if both exist
            // This ensures backward compatibility
        });
        
        // Copy data from hidden to is_hidden if needed
        if (Schema::hasColumn('rooms', 'hidden') && Schema::hasColumn('rooms', 'is_hidden')) {
            DB::statement('UPDATE rooms SET is_hidden = hidden WHERE is_hidden = 0');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'is_hidden')) {
                $table->dropColumn('is_hidden');
            }
        });
    }
};
