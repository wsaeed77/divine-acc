<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts_returns', function (Blueprint $table) {
            $table->string('sa_tax_year', 20)->nullable()->after('sa_notes');
            $table->decimal('sa_tax_amount_due_1', 15, 2)->nullable()->after('sa_tax_year');
            $table->decimal('sa_tax_amount_due_2', 15, 2)->nullable()->after('sa_tax_amount_due_1');
            $table->decimal('sa_tax_amount_due_3', 15, 2)->nullable()->after('sa_tax_amount_due_2');
            $table->text('sa_missing_records')->nullable()->after('sa_tax_amount_due_3');
        });
    }

    public function down(): void
    {
        Schema::table('accounts_returns', function (Blueprint $table) {
            $table->dropColumn([
                'sa_tax_year',
                'sa_tax_amount_due_1',
                'sa_tax_amount_due_2',
                'sa_tax_amount_due_3',
                'sa_missing_records',
            ]);
        });
    }
};
