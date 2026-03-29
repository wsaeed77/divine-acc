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
            ClientTypeSeeder::class,
            CompanyStatusSeeder::class,
            SicCodeSeeder::class,
            ClientManagementLookupSeeder::class,
            ServiceSeeder::class,
            TaskTypeSeeder::class,
            TenantSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
