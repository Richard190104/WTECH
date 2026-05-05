<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $adminUserId = DB::table('users')->insertGetId([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@wtech.sk',
            'password_hash' => Hash::make('admin123'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('carts')->insert([
            'user_id' => $adminUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $adminUser = DB::table('users')->where('email', 'admin@wtech.sk')->first();
        
        if ($adminUser) {
            DB::table('carts')->where('user_id', $adminUser->id)->delete();
            DB::table('users')->where('id', $adminUser->id)->delete();
        }
    }
};
