<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskTypeSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private const ROWS = [
        [
            'slug' => 'accounts_preparation',
            'name' => 'Accounts Preparation',
            'naming_pattern' => 'Accounts Preparation Year End {dd/mm/yyyy}',
            'service_slug' => 'accounts',
            'recurrence' => 'annual',
            'deadline_source' => 'accounts_returns.accounts_period_end',
            'deadline_manual' => false,
            'display_order' => 10,
        ],
        [
            'slug' => 'ch_submission',
            'name' => 'Companies House Submission',
            'naming_pattern' => 'Companies House Submission Year End {dd/mm/yyyy}',
            'service_slug' => 'accounts',
            'recurrence' => 'annual',
            'deadline_source' => 'accounts_returns.ch_accounts_next_due',
            'deadline_manual' => false,
            'display_order' => 20,
        ],
        [
            'slug' => 'ct600_submission',
            'name' => 'CT600 Submission',
            'naming_pattern' => 'CT600 Submission Year End {dd/mm/yyyy}',
            'service_slug' => 'ct600_return',
            'recurrence' => 'annual',
            'deadline_source' => 'accounts_returns.ct600_due',
            'deadline_manual' => false,
            'display_order' => 30,
        ],
        [
            'slug' => 'confirmation_statement',
            'name' => 'Confirmation Statement',
            'naming_pattern' => 'Confirmation Statement Period End {dd/mm/yyyy}',
            'service_slug' => 'confirmation_statement',
            'recurrence' => 'annual',
            'deadline_source' => 'confirmation_statements.statement_due',
            'deadline_manual' => false,
            'display_order' => 40,
        ],
        [
            'slug' => 'vat_submission',
            'name' => 'VAT Submission',
            'naming_pattern' => 'VAT Submission {Frequency} End {dd/mm/yyyy}',
            'service_slug' => 'vat_returns',
            'recurrence' => 'quarterly',
            'deadline_source' => null,
            'deadline_manual' => true,
            'display_order' => 50,
        ],
        [
            'slug' => 'vat_preparation',
            'name' => 'VAT Preparation',
            'naming_pattern' => 'VAT Preparation {Frequency} End {dd/mm/yyyy}',
            'service_slug' => 'vat_returns',
            'recurrence' => 'quarterly',
            'deadline_source' => null,
            'deadline_manual' => true,
            'display_order' => 60,
        ],
        [
            'slug' => 'paye',
            'name' => 'PAYE',
            'naming_pattern' => 'PAYE {Month Year}',
            'service_slug' => 'payroll',
            'recurrence' => 'per_paye_frequency',
            'deadline_source' => null,
            'deadline_manual' => true,
            'display_order' => 70,
        ],
        [
            'slug' => 'cis',
            'name' => 'CIS',
            'naming_pattern' => 'CIS Period End {dd/mm/yyyy}',
            'service_slug' => 'cis',
            'recurrence' => 'monthly',
            'deadline_source' => 'cis_details.cis_deadline',
            'deadline_manual' => false,
            'display_order' => 80,
        ],
        [
            'slug' => 'auto_enrolment',
            'name' => 'Auto-Enrolment',
            'naming_pattern' => 'Auto-Enrolment',
            'service_slug' => 'auto_enrolment',
            'recurrence' => 'one_off',
            'deadline_source' => 'auto_enrolment.staging_date',
            'deadline_manual' => false,
            'display_order' => 90,
        ],
        [
            'slug' => 'p11d',
            'name' => 'P11D Submission',
            'naming_pattern' => 'P11D Submission Year End {dd/mm/yyyy}',
            'service_slug' => 'p11d',
            'recurrence' => 'annual',
            'deadline_source' => 'p11d_details.next_return_due',
            'deadline_manual' => false,
            'display_order' => 100,
        ],
        [
            'slug' => 'bookkeeping',
            'name' => 'Bookkeeping',
            'naming_pattern' => 'Bookkeeping Year End {dd/mm/yyyy}',
            'service_slug' => 'bookkeeping',
            'recurrence' => 'annual',
            'deadline_source' => 'accounts_returns.accounts_period_end',
            'deadline_manual' => false,
            'display_order' => 110,
        ],
        [
            'slug' => 'management_accounts',
            'name' => 'Management Accounts',
            'naming_pattern' => 'Management Accounts Year End {dd/mm/yyyy}',
            'service_slug' => 'management_accounts',
            'recurrence' => 'annual',
            'deadline_source' => 'accounts_returns.accounts_period_end',
            'deadline_manual' => false,
            'display_order' => 120,
        ],
        [
            'slug' => 'self_assessment',
            'name' => 'Self Assessment',
            'naming_pattern' => 'Self Assessment {tax year}',
            'service_slug' => null,
            'recurrence' => 'annual',
            'deadline_source' => null,
            'deadline_manual' => true,
            'display_order' => 130,
        ],
        [
            'slug' => 'pension',
            'name' => 'Pension',
            'naming_pattern' => 'Pension {period}',
            'service_slug' => 'auto_enrolment',
            'recurrence' => 'annual',
            'deadline_source' => null,
            'deadline_manual' => true,
            'display_order' => 140,
        ],
    ];

    public function run(): void
    {
        $now = now();

        foreach (self::ROWS as $row) {
            $serviceId = null;
            if ($row['service_slug'] !== null) {
                $serviceId = Service::query()->where('slug', $row['service_slug'])->value('id');
            }

            DB::table('lkp_task_types')->updateOrInsert(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'naming_pattern' => $row['naming_pattern'],
                    'service_id' => $serviceId,
                    'recurrence' => $row['recurrence'],
                    'deadline_source' => $row['deadline_source'],
                    'deadline_manual' => $row['deadline_manual'],
                    'display_order' => $row['display_order'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
