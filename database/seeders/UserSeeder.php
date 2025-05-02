<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(10)->create();

        $existingIds = DB::table('users')
            ->whereIn('id', [1, 2, 3])
            ->pluck('id')
            ->toArray();

        if (count($existingIds) === 0) {
            DB::table('users')->insert([
                [
                    'id' => 1,
                    'role_id' => 1,
                    'name' => 'admin',
                    'email' => 'denis.klevtsov@bk.ru',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                ],
                [
                    'id' => 2,
                    'role_id' => 2,
                    'name' => 'moderator',
                    'email' => 'denis.klevtsov@gmail.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                ],
                [
                    'id' => 3,
                    'role_id' => 3,
                    'name' => 'user',
                    'email' => 'tesetsetsetstesetsetsets@gmail.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                    'created_at' => now(),
                ]
            ]);

            $this->command->info('Base users inserted.');
        } else {
            $this->command->info('Base users already exist. Seeder skipped.');
        }
    }
}
