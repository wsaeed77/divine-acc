<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientManagementLookupSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (['Mr', 'Mrs', 'Ms', 'Miss', 'Dr', 'Prof', 'Other'] as $name) {
            DB::table('lkp_titles')->updateOrInsert(['name' => $name], ['updated_at' => $now, 'created_at' => $now]);
        }

        foreach (['Single', 'Married', 'Divorced', 'Widowed', 'Civil Partnership'] as $name) {
            DB::table('lkp_marital_statuses')->updateOrInsert(['name' => $name], ['updated_at' => $now, 'created_at' => $now]);
        }

        foreach (['British', 'Irish', 'Pakistani', 'Indian', 'Other'] as $name) {
            DB::table('lkp_nationalities')->updateOrInsert(['name' => $name], ['updated_at' => $now, 'created_at' => $now]);
        }

        foreach (['English', 'Urdu', 'Punjabi', 'Other'] as $name) {
            DB::table('lkp_languages')->updateOrInsert(['name' => $name], ['updated_at' => $now, 'created_at' => $now]);
        }

        $statuses = [
            ['name' => 'Not Started', 'category' => null],
            ['name' => 'In Progress', 'category' => null],
            ['name' => 'Awaiting Info', 'category' => null],
            ['name' => 'Submitted', 'category' => null],
            ['name' => 'Completed', 'category' => null],
        ];
        foreach ($statuses as $row) {
            DB::table('lkp_action_statuses')->updateOrInsert(
                ['name' => $row['name']],
                ['category' => $row['category'], 'updated_at' => $now, 'created_at' => $now]
            );
        }

        foreach (['Cardiff', 'Cumbernauld', 'Nottingham', 'Bristol'] as $name) {
            DB::table('lkp_tax_offices')->updateOrInsert(['name' => $name], ['updated_at' => $now, 'created_at' => $now]);
        }

        foreach (['Quarterly', 'Monthly', 'Annual'] as $name) {
            DB::table('lkp_vat_frequencies')->updateOrInsert(['name' => $name], ['updated_at' => $now, 'created_at' => $now]);
        }

        foreach (['Weekly', 'Monthly', 'Fortnightly', 'Four-Weekly'] as $name) {
            DB::table('lkp_paye_frequencies')->updateOrInsert(['name' => $name], ['updated_at' => $now, 'created_at' => $now]);
        }

        $flatRates = [
            ['name' => 'Retail (not otherwise listed)', 'rate' => 4.00],
            ['name' => 'Business services', 'rate' => 10.50],
            ['name' => 'Accountancy or book-keeping', 'rate' => 14.50],
        ];
        foreach ($flatRates as $row) {
            DB::table('lkp_flat_rate_categories')->updateOrInsert(
                ['name' => $row['name']],
                ['rate' => $row['rate'], 'updated_at' => $now, 'created_at' => $now]
            );
        }

        $memberStates = [
            ['name' => 'United Kingdom', 'code' => 'GB'],
            ['name' => 'Ireland', 'code' => 'IE'],
            ['name' => 'France', 'code' => 'FR'],
            ['name' => 'Germany', 'code' => 'DE'],
        ];
        foreach ($memberStates as $row) {
            DB::table('lkp_vat_member_states')->updateOrInsert(
                ['code' => $row['code']],
                ['name' => $row['name'], 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }
}
