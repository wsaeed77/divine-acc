<?php

namespace Database\Seeders;

use App\Models\ClientType;
use Illuminate\Database\Seeder;

class ClientTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Private Limited Company',
            'Sole Trader',
            'Partnership',
            'Limited Liability Partnership (LLP)',
            'Individual',
            'Charity / Non-profit',
        ];

        foreach ($types as $name) {
            ClientType::query()->firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }
    }
}
