<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Align task deadlines with Accounts & returns “CH accounts next due” where that is the filing target.
     */
    public function up(): void
    {
        $map = [
            'accounts_preparation' => ['deadline_source' => 'accounts_returns.ch_accounts_next_due', 'deadline_manual' => false],
            'bookkeeping' => ['deadline_source' => 'accounts_returns.ch_accounts_next_due', 'deadline_manual' => false],
            'management_accounts' => ['deadline_source' => 'accounts_returns.ch_accounts_next_due', 'deadline_manual' => false],
            'vat_submission' => ['deadline_source' => 'accounts_returns.ch_accounts_next_due', 'deadline_manual' => false],
            'vat_preparation' => ['deadline_source' => 'accounts_returns.ch_accounts_next_due', 'deadline_manual' => false],
        ];

        foreach ($map as $slug => $cols) {
            DB::table('lkp_task_types')->where('slug', $slug)->update(array_merge($cols, [
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('lkp_task_types')->where('slug', 'accounts_preparation')->update([
            'deadline_source' => 'accounts_returns.accounts_period_end',
            'deadline_manual' => false,
            'updated_at' => now(),
        ]);

        foreach (['bookkeeping', 'management_accounts'] as $slug) {
            DB::table('lkp_task_types')->where('slug', $slug)->update([
                'deadline_source' => 'accounts_returns.accounts_period_end',
                'deadline_manual' => false,
                'updated_at' => now(),
            ]);
        }

        foreach (['vat_submission', 'vat_preparation'] as $slug) {
            DB::table('lkp_task_types')->where('slug', $slug)->update([
                'deadline_source' => null,
                'deadline_manual' => true,
                'updated_at' => now(),
            ]);
        }
    }
};
