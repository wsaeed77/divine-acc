<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('trading_name')->nullable();
            $table->text('business_address')->nullable();
            $table->string('nature_of_business')->nullable();
            $table->string('utr', 20)->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::table('accounts_returns', function (Blueprint $table) {
            $table->text('sa_income_overview')->nullable()->after('progress_note');
            $table->text('sa_notes')->nullable()->after('sa_income_overview');
        });

        Schema::table('auto_enrolment', function (Blueprint $table) {
            $table->text('missing_records')->nullable()->after('progress_note');
        });

        Schema::table('p11d_details', function (Blueprint $table) {
            $table->text('missing_records')->nullable()->after('progress_note');
        });
    }

    public function down(): void
    {
        Schema::table('p11d_details', function (Blueprint $table) {
            $table->dropColumn('missing_records');
        });

        Schema::table('auto_enrolment', function (Blueprint $table) {
            $table->dropColumn('missing_records');
        });

        Schema::table('accounts_returns', function (Blueprint $table) {
            $table->dropColumn(['sa_income_overview', 'sa_notes']);
        });

        Schema::dropIfExists('business_details');
    }
};
