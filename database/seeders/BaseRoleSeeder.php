<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BaseRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingIds = DB::table('roles')
            ->whereIn('id', [1, 2, 3])
            ->pluck('id')
            ->toArray();

        if (count($existingIds) === 0) {
            DB::table('roles')->insert([
                ['id' => 1, 'name' => 'admin', 'created_at' => now()],
                ['id' => 2, 'name' => 'moderator', 'created_at' => now()],
                ['id' => 3, 'name' => 'user', 'created_at' => now()],
            ]);
        } else {
            $this->command->info('Base roles already exist. Seeder skipped.');
        }
    }
}
