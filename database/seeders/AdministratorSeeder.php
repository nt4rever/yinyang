<?php

namespace Database\Seeders;

use App\Enums\AccountProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdministratorSeeder extends Seeder
{
    private string $email = 'test@yinyang.io';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('users')->where('email', $this->email)->exists()) {
            return;
        }

        $id = DB::table('users')->insertGetId([
            'id' => (string) Str::uuid7(),
            'name' => 'YinYang Administrator',
            'email' => $this->email,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('accounts')->insertOrIgnore([
            'id' => (string) Str::uuid7(),
            'user_id' => $id,
            'provider' => AccountProvider::LOCAL,
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
