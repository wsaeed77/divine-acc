<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * @var array<int, array{slug: string, name: string}>
     */
    private const SERVICES = [
        ['slug' => 'accounts', 'name' => 'Accounts'],
        ['slug' => 'bookkeeping', 'name' => 'Bookkeeping'],
        ['slug' => 'ct600_return', 'name' => 'CT600 Return'],
        ['slug' => 'payroll', 'name' => 'Payroll'],
        ['slug' => 'auto_enrolment', 'name' => 'Auto-Enrolment'],
        ['slug' => 'vat_returns', 'name' => 'VAT Returns'],
        ['slug' => 'management_accounts', 'name' => 'Management Accounts'],
        ['slug' => 'confirmation_statement', 'name' => 'Confirmation Statement'],
        ['slug' => 'cis', 'name' => 'CIS'],
        ['slug' => 'p11d', 'name' => 'P11D'],
        ['slug' => 'fee_protection', 'name' => 'Fee Protection Service'],
        ['slug' => 'registered_address', 'name' => 'Registered Address'],
        ['slug' => 'bill_payment', 'name' => 'Bill Payment'],
        ['slug' => 'consultation_advice', 'name' => 'Consultation/Advice'],
        ['slug' => 'software', 'name' => 'Software'],
    ];

    public function run(): void
    {
        $now = now();
        foreach (self::SERVICES as $order => $row) {
            DB::table('services')->updateOrInsert(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'display_order' => $order,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
