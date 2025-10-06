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
        // Get all users and split their names
        $users = DB::table('users')->whereNotNull('name')->get();
        
        foreach ($users as $user) {
            $nameParts = explode(' ', trim($user->name));
            
            $firstName = $nameParts[0] ?? '';
            $lastName = '';
            $middleName = '';
            
            if (count($nameParts) > 1) {
                $lastName = array_pop($nameParts); // Last element is last name
                if (count($nameParts) > 1) {
                    // If more than 2 parts, middle elements are middle name
                    array_shift($nameParts); // Remove first name
                    $middleName = implode(' ', $nameParts);
                }
            }
            
            // Update the user with separated names
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'first_name' => $firstName,
                    'middle_name' => $middleName ?: null,
                    'last_name' => $lastName ?: null,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset the name fields
        DB::table('users')->update([
            'first_name' => null,
            'middle_name' => null,
            'last_name' => null,
        ]);
    }
};
