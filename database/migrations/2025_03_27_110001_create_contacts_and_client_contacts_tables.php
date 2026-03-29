<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('title_id')->nullable()->constrained('lkp_titles')->nullOnDelete();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('preferred_name', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('deceased_date')->nullable();
            $table->string('email')->nullable();
            $table->string('portal_login_email')->nullable();
            $table->text('postal_address')->nullable();
            $table->text('previous_address')->nullable();
            $table->string('telephone_number', 30)->nullable();
            $table->string('mobile_number', 30)->nullable();
            $table->string('ni_number', 15)->nullable();
            $table->string('personal_utr', 20)->nullable();
            $table->string('companies_house_personal_code', 20)->nullable();
            $table->date('terms_signed_date')->nullable();
            $table->boolean('photo_id_verified')->default(false);
            $table->boolean('address_verified')->default(false);
            $table->foreignId('marital_status_id')->nullable()->constrained('lkp_marital_statuses')->nullOnDelete();
            $table->foreignId('nationality_id')->nullable()->constrained('lkp_nationalities')->nullOnDelete();
            $table->foreignId('language_id')->nullable()->constrained('lkp_languages')->nullOnDelete();
            $table->boolean('aml_check_started')->default(false);
            $table->date('aml_check_date')->nullable();
            $table->boolean('id_check_started')->default(false);
            $table->date('id_check_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'last_name', 'first_name']);
        });

        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_main_contact')->default(false);
            $table->boolean('create_self_assessment')->default(false);
            $table->decimal('self_assessment_fee', 10, 2)->nullable();
            $table->boolean('client_does_own_sa')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['client_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_contacts');
        Schema::dropIfExists('contacts');
    }
};
