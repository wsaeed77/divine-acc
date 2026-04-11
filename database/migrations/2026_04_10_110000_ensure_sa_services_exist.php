<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $saServices = [
            ['slug' => 'main_contact_sa', 'name' => 'Main Contact SA'],
            ['slug' => 'self_assessment_tax_return', 'name' => 'Self Assessment Tax Return'],
            ['slug' => 'mtd_quarterly_filing', 'name' => 'MTD Quarterly Filing'],
            ['slug' => 'mtd_final_declaration', 'name' => 'MTD Final Declaration'],
        ];

        $maxOrder = (int) DB::table('services')->max('display_order');

        foreach ($saServices as $i => $row) {
            DB::table('services')->updateOrInsert(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'display_order' => $maxOrder + $i + 1,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        // Keep services — they may be referenced by client_services rows.
    }
};
