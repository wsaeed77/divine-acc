<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->text('income_details')->nullable()->after('is_active');
            $table->string('previous_accountant_name', 255)->nullable();
            $table->text('previous_accountant_details')->nullable();
            $table->text('other_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'income_details',
                'previous_accountant_name',
                'previous_accountant_details',
                'other_details',
            ]);
        });
    }
};
