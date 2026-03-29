<?php

namespace Database\Seeders;

use App\Models\CompanyStatus;
use Illuminate\Database\Seeder;

class CompanyStatusSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Active',
            'Dormant',
            'Dissolved',
            'In Liquidation',
            'In Administration',
        ] as $name) {
            CompanyStatus::query()->firstOrCreate(['name' => $name]);
        }
    }
}
