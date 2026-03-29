<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('company_number', 20)->nullable();
            $table->foreignId('company_status_id')->nullable()->constrained('lkp_company_statuses')->nullOnDelete();
            $table->date('incorporation_date')->nullable();
            $table->string('trading_as')->nullable();
            $table->text('registered_address')->nullable();
            $table->text('postal_address')->nullable();
            $table->string('invoice_address_type', 20)->default('postal');
            $table->text('invoice_address_custom')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('email_domain')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->decimal('turnover', 15, 2)->nullable();
            $table->date('date_of_trading')->nullable();
            $table->foreignId('sic_code_id')->nullable()->constrained('lkp_sic_codes')->nullOnDelete();
            $table->string('nature_of_business')->nullable();
            $table->string('corporation_tax_office', 150)->nullable();
            $table->string('company_utr', 20)->nullable();
            $table->string('companies_house_auth_code', 20)->nullable();
            $table->timestamps();

            $table->index('company_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_details');
    }
};
