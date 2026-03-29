<?php

namespace Database\Seeders;

use App\Models\SicCode;
use Illuminate\Database\Seeder;

class SicCodeSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['62012', 'Business and domestic software development'],
            ['69201', 'Accounting, auditing and bookkeeping activities'],
            ['69202', 'Bookkeeping activities'],
            ['69203', 'Tax consultancy'],
            ['70229', 'Management consultancy activities other than financial management'],
            ['82990', 'Other business support service activities not elsewhere classified'],
        ];

        foreach ($rows as [$code, $description]) {
            SicCode::query()->firstOrCreate(
                ['code' => $code],
                ['description' => $description]
            );
        }
    }
}
