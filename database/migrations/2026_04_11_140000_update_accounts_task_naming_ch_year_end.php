<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $patterns = [
            'accounts_preparation' => 'Accounts Preparation CH year end {dd/mm/yyyy}',
            'bookkeeping' => 'Bookkeeping CH year end {dd/mm/yyyy}',
            'management_accounts' => 'Management Accounts CH year end {dd/mm/yyyy}',
        ];

        foreach ($patterns as $slug => $pattern) {
            DB::table('lkp_task_types')->where('slug', $slug)->update([
                'naming_pattern' => $pattern,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $patterns = [
            'accounts_preparation' => 'Accounts Preparation Year End {dd/mm/yyyy}',
            'bookkeeping' => 'Bookkeeping Year End {dd/mm/yyyy}',
            'management_accounts' => 'Management Accounts Year End {dd/mm/yyyy}',
        ];

        foreach ($patterns as $slug => $pattern) {
            DB::table('lkp_task_types')->where('slug', $slug)->update([
                'naming_pattern' => $pattern,
                'updated_at' => now(),
            ]);
        }
    }
};
