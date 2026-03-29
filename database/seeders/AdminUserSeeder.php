<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the tenant admin user for the demo tenant (requires TenantSeeder).
     *
     * Set SEED_ADMIN_PASSWORD in .env to override the default (local dev only).
     */
    public function run(): void
    {
        $tenant = Tenant::query()->where('slug', 'demo-accountancy')->first();

        if (! $tenant) {
            if ($this->command) {
                $this->command->warn('No tenant with slug "demo-accountancy". Run TenantSeeder first.');
            }

            return;
        }

        $email = env('SEED_ADMIN_EMAIL', 'admin@demo-accountancy.test');
        $passwordPlain = env('SEED_ADMIN_PASSWORD', 'password');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'tenant_id' => $tenant->id,
                'name' => env('SEED_ADMIN_NAME', 'Demo Admin'),
                'password' => Hash::make($passwordPlain),
                'role' => User::ROLE_TENANT_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        if ($this->command) {
            $this->command->info("Admin user seeded: {$email} (tenant admin)");
            $this->command->warn('Default password is set by SEED_ADMIN_PASSWORD or "password" — change after first login.');
        }
    }
}
