<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Seed a single demo tenant (accounting firm workspace).
     */
    public function run(): void
    {
        Tenant::query()->updateOrCreate(
            ['slug' => 'demo-accountancy'],
            [
                'name' => 'Demo Accountancy Ltd',
                'email' => 'firm@demo-accountancy.test',
                'phone' => '+44 20 7946 0000',
                'address' => "1 Example Street\nLondon\nEC1A 1BB",
                'logo_path' => null,
                'primary_color' => '#0f766e',
                'settings' => null,
            ]
        );

        if ($this->command) {
            $this->command->info('Tenant seeded: Demo Accountancy Ltd (slug: demo-accountancy)');
        }
    }
}
