<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndAssignSeeder::class, // 👈 chama o seeder que você criou
            PermissionSeeder::class,
            
        ]);
    }
}
