<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->date('accounts_period_end')->nullable();
            $table->date('ch_year_end')->nullable();
            $table->date('hmrc_year_end')->nullable();
            $table->date('ch_accounts_next_due')->nullable();
            $table->date('ct600_due')->nullable();
            $table->decimal('corporation_tax_amount_due', 12, 2)->nullable();
            $table->date('tax_due_hmrc_year_end')->nullable();
            $table->string('ct_payment_reference', 50)->nullable();
            $table->foreignId('tax_office_id')->nullable()->constrained('lkp_tax_offices')->nullOnDelete();
            $table->boolean('ch_email_reminder')->default(false);
            $table->foreignId('latest_action_id')->nullable()->constrained('lkp_action_statuses')->nullOnDelete();
            $table->date('latest_action_date')->nullable();
            $table->date('records_received')->nullable();
            $table->text('progress_note')->nullable();
            $table->timestamps();
        });

        Schema::create('confirmation_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->date('statement_date')->nullable();
            $table->date('statement_due')->nullable();
            $table->foreignId('latest_action_id')->nullable()->constrained('lkp_action_statuses')->nullOnDelete();
            $table->date('latest_action_date')->nullable();
            $table->date('records_received')->nullable();
            $table->text('progress_note')->nullable();
            $table->text('officers')->nullable();
            $table->text('share_capital')->nullable();
            $table->text('shareholders')->nullable();
            $table->text('people_with_significant_control')->nullable();
            $table->timestamps();
        });

        Schema::create('vat_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->foreignId('vat_frequency_id')->nullable()->constrained('lkp_vat_frequencies')->nullOnDelete();
            $table->date('vat_period_end')->nullable();
            $table->date('next_return_due')->nullable();
            $table->decimal('vat_bill_amount', 12, 2)->nullable();
            $table->date('vat_bill_due')->nullable();
            $table->foreignId('latest_action_id')->nullable()->constrained('lkp_action_statuses')->nullOnDelete();
            $table->date('latest_action_date')->nullable();
            $table->date('records_received')->nullable();
            $table->text('progress_note')->nullable();
            $table->foreignId('vat_member_state_id')->nullable()->constrained('lkp_vat_member_states')->nullOnDelete();
            $table->string('vat_number', 20)->nullable();
            $table->text('vat_address')->nullable();
            $table->date('date_of_registration')->nullable();
            $table->date('effective_date')->nullable();
            $table->decimal('estimated_turnover', 15, 2)->nullable();
            $table->date('applied_for_mtd')->nullable();
            $table->boolean('mtd_ready')->default(false);
            $table->boolean('transfer_of_going_concern')->default(false);
            $table->boolean('involved_in_other_businesses')->default(false);
            $table->boolean('direct_debit')->default(false);
            $table->boolean('standard_scheme')->default(false);
            $table->boolean('cash_accounting_scheme')->default(false);
            $table->boolean('retail_scheme')->default(false);
            $table->boolean('margin_scheme')->default(false);
            $table->boolean('flat_rate')->default(false);
            $table->foreignId('flat_rate_category_id')->nullable()->constrained('lkp_flat_rate_categories')->nullOnDelete();
            $table->unsignedTinyInteger('month_last_quarter_submitted')->nullable();
            $table->decimal('box5_last_quarter_submitted', 12, 2)->nullable();
            $table->text('general_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('paye_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('employers_reference', 50)->nullable();
            $table->string('accounts_office_reference', 50)->nullable();
            $table->string('years_required', 20)->nullable();
            $table->foreignId('paye_frequency_id')->nullable()->constrained('lkp_paye_frequencies')->nullOnDelete();
            $table->boolean('irregular_monthly_pay')->default(false);
            $table->boolean('nil_eps')->default(false);
            $table->unsignedInteger('no_of_employees')->nullable();
            $table->text('salary_details')->nullable();
            $table->date('first_pay_date')->nullable();
            $table->date('rti_deadline')->nullable();
            $table->date('paye_scheme_ceased')->nullable();
            $table->foreignId('latest_action_id')->nullable()->constrained('lkp_action_statuses')->nullOnDelete();
            $table->date('latest_action_date')->nullable();
            $table->date('records_received')->nullable();
            $table->text('progress_note')->nullable();
            $table->text('general_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cis_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->boolean('is_contractor')->default(false);
            $table->boolean('is_subcontractor')->default(false);
            $table->date('cis_date')->nullable();
            $table->date('cis_deadline')->nullable();
            $table->foreignId('latest_action_id')->nullable()->constrained('lkp_action_statuses')->nullOnDelete();
            $table->date('latest_action_date')->nullable();
            $table->date('records_received')->nullable();
            $table->text('progress_note')->nullable();
            $table->timestamps();
        });

        Schema::create('auto_enrolment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->foreignId('latest_action_id')->nullable()->constrained('lkp_action_statuses')->nullOnDelete();
            $table->date('latest_action_date')->nullable();
            $table->date('records_received')->nullable();
            $table->text('progress_note')->nullable();
            $table->date('staging_date')->nullable();
            $table->date('postponement_date')->nullable();
            $table->date('pensions_regulator_opt_out_date')->nullable();
            $table->date('re_enrolment_date')->nullable();
            $table->string('pension_provider', 150)->nullable();
            $table->string('pension_id', 50)->nullable();
            $table->date('declaration_of_compliance_due')->nullable();
            $table->date('declaration_of_compliance_submission')->nullable();
            $table->timestamps();
        });

        Schema::create('p11d_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->date('next_return_due')->nullable();
            $table->date('latest_submitted')->nullable();
            $table->foreignId('latest_action_id')->nullable()->constrained('lkp_action_statuses')->nullOnDelete();
            $table->date('latest_action_date')->nullable();
            $table->date('records_received')->nullable();
            $table->text('progress_note')->nullable();
            $table->timestamps();
        });

        Schema::create('registration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete()->unique();
            $table->boolean('terms_signed_fee_paid')->default(false);
            $table->decimal('registration_fee', 10, 2)->nullable();
            $table->date('letter_of_engagement_signed')->nullable();
            $table->boolean('money_laundering_complete')->default(false);
            $table->date('sixty_four_eight_registration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration');
        Schema::dropIfExists('p11d_details');
        Schema::dropIfExists('auto_enrolment');
        Schema::dropIfExists('cis_details');
        Schema::dropIfExists('paye_details');
        Schema::dropIfExists('vat_details');
        Schema::dropIfExists('confirmation_statements');
        Schema::dropIfExists('accounts_returns');
    }
};
