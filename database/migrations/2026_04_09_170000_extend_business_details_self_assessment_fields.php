<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->date('commenced_trading')->nullable()->after('business_address');
            $table->date('ceased_trading')->nullable()->after('commenced_trading');
            $table->date('registered_for_sa')->nullable()->after('ceased_trading');
            $table->decimal('turnover', 15, 2)->nullable()->after('registered_for_sa');
            $table->string('mtd_qualifying_year', 20)->nullable()->after('nature_of_business');
        });
    }

    public function down(): void
    {
        Schema::table('business_details', function (Blueprint $table) {
            $table->dropColumn([
                'commenced_trading',
                'ceased_trading',
                'registered_for_sa',
                'turnover',
                'mtd_qualifying_year',
            ]);
        });
    }
};
