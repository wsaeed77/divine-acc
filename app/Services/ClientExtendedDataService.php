<?php

namespace App\Services;

use App\Models\AccountsReturn;
use App\Models\AutoEnrolment;
use App\Models\CisDetail;
use App\Models\Client;
use App\Models\ClientCombinedPricing;
use App\Models\ClientRegistration;
use App\Models\ClientService;
use App\Models\ConfirmationStatement;
use App\Models\Contact;
use App\Models\P11dDetail;
use App\Models\PayeDetail;
use App\Models\Service;
use App\Models\VatDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientExtendedDataService
{
    public function __construct(
        private ClientTaskSyncService $clientTaskSync,
    ) {}

    public function ensureBootstrapped(Client $client): void
    {
        if ($client->clientServices()->count() === 0) {
            $this->bootstrapForNewClient($client);
        }
    }

    public function bootstrapForNewClient(Client $client): void
    {
        DB::transaction(function () use ($client) {
            foreach (Service::query()->orderBy('display_order')->orderBy('name')->get() as $service) {
                ClientService::query()->firstOrCreate(
                    ['client_id' => $client->id, 'service_id' => $service->id],
                    ['is_enabled' => false, 'fee' => null]
                );
            }

            ClientCombinedPricing::query()->firstOrCreate(['client_id' => $client->id]);
            AccountsReturn::query()->firstOrCreate(['client_id' => $client->id]);
            ConfirmationStatement::query()->firstOrCreate(['client_id' => $client->id]);
            VatDetail::query()->firstOrCreate(['client_id' => $client->id]);
            PayeDetail::query()->firstOrCreate(['client_id' => $client->id]);
            CisDetail::query()->firstOrCreate(['client_id' => $client->id]);
            AutoEnrolment::query()->firstOrCreate(['client_id' => $client->id]);
            P11dDetail::query()->firstOrCreate(['client_id' => $client->id]);
            ClientRegistration::query()->firstOrCreate(['client_id' => $client->id]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function buildFormPayload(Client $client): array
    {
        $this->ensureBootstrapped($client);

        $client->load([
            'clientServices.service',
            'combinedPricing',
            'contacts',
            'accountsReturn',
            'confirmationStatement',
            'vatDetail',
            'payeDetail',
            'cisDetail',
            'autoEnrolment',
            'p11dDetail',
            'clientRegistration',
        ]);

        $services = $client->clientServices->map(fn (ClientService $cs) => [
            'service_id' => $cs->service_id,
            'name' => $cs->service->name,
            'slug' => $cs->service->slug,
            'is_enabled' => $cs->is_enabled,
            'fee' => $cs->fee,
        ])->values()->all();

        $mainContact = $client->contacts->firstWhere(fn ($c) => (bool) $c->pivot->is_main_contact);
        $secondaryContact = $client->contacts->first(fn ($c) => ! (bool) $c->pivot->is_main_contact);

        return [
            'combined_pricing' => $this->mapCombinedPricing($client->combinedPricing),
            'services' => $services,
            'main_contact' => $mainContact ? $this->mapContact($mainContact) : $this->emptyMainContact(),
            'secondary_contact' => $secondaryContact ? $this->mapSecondaryContact($secondaryContact) : $this->emptySecondaryContact(),
            'accounts_returns' => $this->mapAccountsReturn($client->accountsReturn),
            'confirmation_statement' => $this->mapConfirmationStatement($client->confirmationStatement),
            'vat' => $this->mapVatDetail($client->vatDetail),
            'paye' => $this->mapPayeDetail($client->payeDetail),
            'cis' => $this->mapCisDetail($client->cisDetail),
            'auto_enrolment' => $this->mapAutoEnrolment($client->autoEnrolment),
            'p11d' => $this->mapP11dDetail($client->p11dDetail),
            'registration' => $this->mapRegistration($client->clientRegistration),
        ];
    }

    /**
     * Default extended payload for the client create form (no client row yet).
     *
     * @return array<string, mixed>
     */
    public function emptyExtendedFormPayload(): array
    {
        $services = Service::query()->orderBy('display_order')->orderBy('name')->get()->map(fn (Service $s) => [
            'service_id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'is_enabled' => false,
            'fee' => null,
        ])->values()->all();

        return [
            'combined_pricing' => [
                'annual_charge_enabled' => false,
                'annual_charge' => '',
                'monthly_charge_enabled' => false,
                'monthly_charge' => '',
            ],
            'services' => $services,
            'main_contact' => $this->emptyMainContact(),
            'secondary_contact' => $this->emptySecondaryContact(),
            'accounts_returns' => $this->emptyAccountsReturnsForm(),
            'confirmation_statement' => $this->emptyConfirmationStatementForm(),
            'vat' => $this->emptyVatForm(),
            'paye' => $this->emptyPayeForm(),
            'cis' => $this->emptyCisForm(),
            'auto_enrolment' => $this->emptyAutoEnrolmentForm(),
            'p11d' => $this->emptyP11dForm(),
            'registration' => $this->emptyRegistrationForm(),
        ];
    }

    public function syncFromRequest(Request $request, Client $client): void
    {
        $tenantId = (int) $request->user()->tenant_id;

        $validated = $request->validate($this->validationRules($tenantId));

        DB::transaction(function () use ($validated, $client, $tenantId) {
            if (isset($validated['combined_pricing'])) {
                $client->combinedPricing()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyCombinedPricing($validated['combined_pricing'])
                );
            }

            if (isset($validated['services'])) {
                foreach ($validated['services'] as $row) {
                    ClientService::query()
                        ->where('client_id', $client->id)
                        ->where('service_id', $row['service_id'])
                        ->update([
                            'is_enabled' => (bool) ($row['is_enabled'] ?? false),
                            'fee' => $row['fee'] ?? null,
                        ]);
                }
            }

            if (isset($validated['accounts_returns'])) {
                $client->accountsReturn()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyAccountsReturn($validated['accounts_returns'])
                );
            }

            if (isset($validated['confirmation_statement'])) {
                $client->confirmationStatement()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyConfirmationStatement($validated['confirmation_statement'])
                );
            }

            if (isset($validated['vat'])) {
                $client->vatDetail()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyVatDetail($validated['vat'])
                );
            }

            if (isset($validated['paye'])) {
                $client->payeDetail()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyPayeDetail($validated['paye'])
                );
            }

            if (isset($validated['cis'])) {
                $client->cisDetail()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyCisDetail($validated['cis'])
                );
            }

            if (isset($validated['auto_enrolment'])) {
                $client->autoEnrolment()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyAutoEnrolment($validated['auto_enrolment'])
                );
            }

            if (isset($validated['p11d'])) {
                $client->p11dDetail()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyP11dDetail($validated['p11d'])
                );
            }

            if (isset($validated['registration'])) {
                $client->clientRegistration()->updateOrCreate(
                    ['client_id' => $client->id],
                    $this->onlyRegistration($validated['registration'])
                );
            }

            if (isset($validated['main_contact'])) {
                $this->syncMainContact($client, $tenantId, $validated['main_contact']);
            }

            if (isset($validated['secondary_contact'])) {
                $mainId = $client->fresh()->contacts->firstWhere(fn ($c) => (bool) $c->pivot->is_main_contact)?->id;
                $this->syncSecondaryContact($client, $tenantId, $validated['secondary_contact'], $mainId);
            }

            $this->clientTaskSync->syncForClient($client->fresh());
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncMainContact(Client $client, int $tenantId, array $data): void
    {
        $first = trim((string) ($data['first_name'] ?? ''));
        $last = trim((string) ($data['last_name'] ?? ''));

        if ($first === '' && $last === '') {
            return;
        }

        $payload = [
            'tenant_id' => $tenantId,
            'title_id' => $data['title_id'] ?: null,
            'first_name' => $first !== '' ? $first : '—',
            'middle_name' => $data['middle_name'] ?: null,
            'last_name' => $last !== '' ? $last : '—',
            'preferred_name' => $data['preferred_name'] ?: null,
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'deceased_date' => $data['deceased_date'] ?: null,
            'email' => $data['email'] ?: null,
            'portal_login_email' => $data['portal_login_email'] ?: null,
            'postal_address' => $data['postal_address'] ?: null,
            'previous_address' => $data['previous_address'] ?: null,
            'telephone_number' => $data['telephone_number'] ?: null,
            'mobile_number' => $data['mobile_number'] ?: null,
            'ni_number' => $data['ni_number'] ?: null,
            'personal_utr' => $data['personal_utr'] ?: null,
            'companies_house_personal_code' => $data['companies_house_personal_code'] ?: null,
            'terms_signed_date' => $data['terms_signed_date'] ?: null,
            'photo_id_verified' => (bool) ($data['photo_id_verified'] ?? false),
            'address_verified' => (bool) ($data['address_verified'] ?? false),
            'marital_status_id' => $data['marital_status_id'] ?: null,
            'nationality_id' => $data['nationality_id'] ?: null,
            'language_id' => $data['language_id'] ?: null,
            'aml_check_started' => (bool) ($data['aml_check_started'] ?? false),
            'aml_check_date' => $data['aml_check_date'] ?: null,
            'id_check_started' => (bool) ($data['id_check_started'] ?? false),
            'id_check_date' => $data['id_check_date'] ?: null,
        ];

        if (! empty($data['id'])) {
            $contact = Contact::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($data['id'])
                ->firstOrFail();
            $contact->update($payload);
        } else {
            $contact = Contact::query()->create($payload);
        }

        $pivot = [
            'is_main_contact' => true,
            'create_self_assessment' => (bool) ($data['create_self_assessment'] ?? false),
            'self_assessment_fee' => $data['self_assessment_fee'] ?? null,
            'client_does_own_sa' => (bool) ($data['client_does_own_sa'] ?? false),
        ];

        DB::table('client_contacts')->where('client_id', $client->id)->update(['is_main_contact' => false]);

        if ($client->contacts()->where('contacts.id', $contact->id)->exists()) {
            $client->contacts()->updateExistingPivot($contact->id, $pivot);
        } else {
            $client->contacts()->attach($contact->id, $pivot);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncSecondaryContact(Client $client, int $tenantId, array $data, ?int $mainContactId): void
    {
        $first = trim((string) ($data['first_name'] ?? ''));
        $last = trim((string) ($data['last_name'] ?? ''));

        $existingId = ! empty($data['id']) ? (int) $data['id'] : null;

        if ($first === '' && $last === '') {
            if ($existingId) {
                $existing = Contact::query()
                    ->where('tenant_id', $tenantId)
                    ->whereKey($existingId)
                    ->first();
                if ($existing && (int) $existing->id !== (int) $mainContactId) {
                    $client->contacts()->detach($existing->id);
                }
            }

            return;
        }

        $payload = [
            'tenant_id' => $tenantId,
            'title_id' => $data['title_id'] ?: null,
            'first_name' => $first !== '' ? $first : '—',
            'middle_name' => $data['middle_name'] ?: null,
            'last_name' => $last !== '' ? $last : '—',
            'preferred_name' => $data['preferred_name'] ?: null,
            'date_of_birth' => $data['date_of_birth'] ?: null,
            'deceased_date' => $data['deceased_date'] ?: null,
            'email' => $data['email'] ?: null,
            'portal_login_email' => $data['portal_login_email'] ?: null,
            'postal_address' => $data['postal_address'] ?: null,
            'previous_address' => $data['previous_address'] ?: null,
            'telephone_number' => $data['telephone_number'] ?: null,
            'mobile_number' => $data['mobile_number'] ?: null,
            'ni_number' => $data['ni_number'] ?: null,
            'personal_utr' => $data['personal_utr'] ?: null,
            'companies_house_personal_code' => $data['companies_house_personal_code'] ?: null,
            'terms_signed_date' => $data['terms_signed_date'] ?: null,
            'photo_id_verified' => (bool) ($data['photo_id_verified'] ?? false),
            'address_verified' => (bool) ($data['address_verified'] ?? false),
            'marital_status_id' => $data['marital_status_id'] ?: null,
            'nationality_id' => $data['nationality_id'] ?: null,
            'language_id' => $data['language_id'] ?: null,
            'aml_check_started' => (bool) ($data['aml_check_started'] ?? false),
            'aml_check_date' => $data['aml_check_date'] ?: null,
            'id_check_started' => (bool) ($data['id_check_started'] ?? false),
            'id_check_date' => $data['id_check_date'] ?: null,
        ];

        if ($existingId) {
            $contact = Contact::query()
                ->where('tenant_id', $tenantId)
                ->whereKey($existingId)
                ->firstOrFail();
            if ((int) $contact->id === (int) $mainContactId) {
                return;
            }
            $contact->update($payload);
        } else {
            $contact = Contact::query()->create($payload);
        }

        $pivot = [
            'is_main_contact' => false,
            'create_self_assessment' => false,
            'self_assessment_fee' => null,
            'client_does_own_sa' => false,
        ];

        $others = $client->contacts()->where('contacts.id', '!=', $contact->id)->get();
        foreach ($others as $o) {
            if (! $o->pivot->is_main_contact) {
                $client->contacts()->detach($o->id);
            }
        }

        if ($client->contacts()->where('contacts.id', $contact->id)->exists()) {
            $client->contacts()->updateExistingPivot($contact->id, $pivot);
        } else {
            $client->contacts()->attach($contact->id, $pivot);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCombinedPricing(?ClientCombinedPricing $m): array
    {
        if (! $m) {
            return [
                'annual_charge_enabled' => false,
                'annual_charge' => '',
                'monthly_charge_enabled' => false,
                'monthly_charge' => '',
            ];
        }

        return [
            'annual_charge_enabled' => $m->annual_charge_enabled,
            'annual_charge' => $m->annual_charge,
            'monthly_charge_enabled' => $m->monthly_charge_enabled,
            'monthly_charge' => $m->monthly_charge,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapContact(Contact $c): array
    {
        return array_merge($this->mapContactCore($c), [
            'create_self_assessment' => (bool) $c->pivot->create_self_assessment,
            'self_assessment_fee' => $c->pivot->self_assessment_fee,
            'client_does_own_sa' => (bool) $c->pivot->client_does_own_sa,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSecondaryContact(Contact $c): array
    {
        return $this->mapContactCore($c);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapContactCore(Contact $c): array
    {
        return [
            'id' => $c->id,
            'title_id' => $c->title_id ? (string) $c->title_id : '',
            'first_name' => $c->first_name,
            'middle_name' => $c->middle_name,
            'last_name' => $c->last_name,
            'preferred_name' => $c->preferred_name,
            'date_of_birth' => $c->date_of_birth?->format('Y-m-d'),
            'deceased_date' => $c->deceased_date?->format('Y-m-d'),
            'email' => $c->email,
            'portal_login_email' => $c->portal_login_email,
            'postal_address' => $c->postal_address,
            'previous_address' => $c->previous_address,
            'telephone_number' => $c->telephone_number,
            'mobile_number' => $c->mobile_number,
            'ni_number' => $c->ni_number,
            'personal_utr' => $c->personal_utr,
            'companies_house_personal_code' => $c->companies_house_personal_code,
            'terms_signed_date' => $c->terms_signed_date?->format('Y-m-d'),
            'photo_id_verified' => (bool) $c->photo_id_verified,
            'address_verified' => (bool) $c->address_verified,
            'marital_status_id' => $c->marital_status_id ? (string) $c->marital_status_id : '',
            'nationality_id' => $c->nationality_id ? (string) $c->nationality_id : '',
            'language_id' => $c->language_id ? (string) $c->language_id : '',
            'aml_check_started' => (bool) $c->aml_check_started,
            'aml_check_date' => $c->aml_check_date?->format('Y-m-d'),
            'id_check_started' => (bool) $c->id_check_started,
            'id_check_date' => $c->id_check_date?->format('Y-m-d'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyMainContact(): array
    {
        return array_merge($this->emptyContactCore(), [
            'create_self_assessment' => false,
            'self_assessment_fee' => '',
            'client_does_own_sa' => false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptySecondaryContact(): array
    {
        return $this->emptyContactCore();
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyContactCore(): array
    {
        return [
            'id' => null,
            'title_id' => '',
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'preferred_name' => '',
            'date_of_birth' => '',
            'deceased_date' => '',
            'email' => '',
            'portal_login_email' => '',
            'postal_address' => '',
            'previous_address' => '',
            'telephone_number' => '',
            'mobile_number' => '',
            'ni_number' => '',
            'personal_utr' => '',
            'companies_house_personal_code' => '',
            'terms_signed_date' => '',
            'photo_id_verified' => false,
            'address_verified' => false,
            'marital_status_id' => '',
            'nationality_id' => '',
            'language_id' => '',
            'aml_check_started' => false,
            'aml_check_date' => '',
            'id_check_started' => false,
            'id_check_date' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyAccountsReturnsForm(): array
    {
        return [
            'accounts_period_end' => '',
            'ch_year_end' => '',
            'hmrc_year_end' => '',
            'ch_accounts_next_due' => '',
            'ct600_due' => '',
            'corporation_tax_amount_due' => '',
            'tax_due_hmrc_year_end' => '',
            'ct_payment_reference' => '',
            'tax_office_id' => '',
            'ch_email_reminder' => false,
            'latest_action_id' => '',
            'latest_action_date' => '',
            'records_received' => '',
            'progress_note' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyConfirmationStatementForm(): array
    {
        return [
            'statement_date' => '',
            'statement_due' => '',
            'latest_action_id' => '',
            'latest_action_date' => '',
            'records_received' => '',
            'progress_note' => '',
            'officers' => '',
            'share_capital' => '',
            'shareholders' => '',
            'people_with_significant_control' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyVatForm(): array
    {
        return [
            'vat_frequency_id' => '',
            'vat_period_end' => '',
            'next_return_due' => '',
            'vat_bill_amount' => '',
            'vat_bill_due' => '',
            'latest_action_id' => '',
            'latest_action_date' => '',
            'records_received' => '',
            'progress_note' => '',
            'vat_member_state_id' => '',
            'vat_number' => '',
            'vat_address' => '',
            'date_of_registration' => '',
            'effective_date' => '',
            'estimated_turnover' => '',
            'applied_for_mtd' => '',
            'mtd_ready' => false,
            'transfer_of_going_concern' => false,
            'involved_in_other_businesses' => false,
            'direct_debit' => false,
            'standard_scheme' => false,
            'cash_accounting_scheme' => false,
            'retail_scheme' => false,
            'margin_scheme' => false,
            'flat_rate' => false,
            'flat_rate_category_id' => '',
            'month_last_quarter_submitted' => '',
            'box5_last_quarter_submitted' => '',
            'general_notes' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayeForm(): array
    {
        return [
            'employers_reference' => '',
            'accounts_office_reference' => '',
            'years_required' => '',
            'paye_frequency_id' => '',
            'irregular_monthly_pay' => false,
            'nil_eps' => false,
            'no_of_employees' => '',
            'salary_details' => '',
            'first_pay_date' => '',
            'rti_deadline' => '',
            'paye_scheme_ceased' => '',
            'latest_action_id' => '',
            'latest_action_date' => '',
            'records_received' => '',
            'progress_note' => '',
            'general_notes' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyCisForm(): array
    {
        return [
            'is_contractor' => false,
            'is_subcontractor' => false,
            'cis_date' => '',
            'cis_deadline' => '',
            'latest_action_id' => '',
            'latest_action_date' => '',
            'records_received' => '',
            'progress_note' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyAutoEnrolmentForm(): array
    {
        return [
            'latest_action_id' => '',
            'latest_action_date' => '',
            'records_received' => '',
            'progress_note' => '',
            'staging_date' => '',
            'postponement_date' => '',
            'pensions_regulator_opt_out_date' => '',
            're_enrolment_date' => '',
            'pension_provider' => '',
            'pension_id' => '',
            'declaration_of_compliance_due' => '',
            'declaration_of_compliance_submission' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyP11dForm(): array
    {
        return [
            'next_return_due' => '',
            'latest_submitted' => '',
            'latest_action_id' => '',
            'latest_action_date' => '',
            'records_received' => '',
            'progress_note' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRegistrationForm(): array
    {
        return [
            'terms_signed_fee_paid' => false,
            'registration_fee' => '',
            'letter_of_engagement_signed' => '',
            'money_laundering_complete' => false,
            'sixty_four_eight_registration' => '',
        ];
    }

    private function mapAccountsReturn(?AccountsReturn $m): array
    {
        if (! $m) {
            return [];
        }

        return [
            'accounts_period_end' => $m->accounts_period_end?->format('Y-m-d'),
            'ch_year_end' => $m->ch_year_end?->format('Y-m-d'),
            'hmrc_year_end' => $m->hmrc_year_end?->format('Y-m-d'),
            'ch_accounts_next_due' => $m->ch_accounts_next_due?->format('Y-m-d'),
            'ct600_due' => $m->ct600_due?->format('Y-m-d'),
            'corporation_tax_amount_due' => $m->corporation_tax_amount_due,
            'tax_due_hmrc_year_end' => $m->tax_due_hmrc_year_end?->format('Y-m-d'),
            'ct_payment_reference' => $m->ct_payment_reference,
            'tax_office_id' => $m->tax_office_id ? (string) $m->tax_office_id : '',
            'ch_email_reminder' => $m->ch_email_reminder,
            'latest_action_id' => $m->latest_action_id ? (string) $m->latest_action_id : '',
            'latest_action_date' => $m->latest_action_date?->format('Y-m-d'),
            'records_received' => $m->records_received?->format('Y-m-d'),
            'progress_note' => $m->progress_note,
        ];
    }

    private function mapConfirmationStatement(?ConfirmationStatement $m): array
    {
        if (! $m) {
            return [];
        }

        return [
            'statement_date' => $m->statement_date?->format('Y-m-d'),
            'statement_due' => $m->statement_due?->format('Y-m-d'),
            'latest_action_id' => $m->latest_action_id ? (string) $m->latest_action_id : '',
            'latest_action_date' => $m->latest_action_date?->format('Y-m-d'),
            'records_received' => $m->records_received?->format('Y-m-d'),
            'progress_note' => $m->progress_note,
            'officers' => $m->officers,
            'share_capital' => $m->share_capital,
            'shareholders' => $m->shareholders,
            'people_with_significant_control' => $m->people_with_significant_control,
        ];
    }

    private function mapVatDetail(?VatDetail $m): array
    {
        if (! $m) {
            return [];
        }

        return [
            'vat_frequency_id' => $m->vat_frequency_id ? (string) $m->vat_frequency_id : '',
            'vat_period_end' => $m->vat_period_end?->format('Y-m-d'),
            'next_return_due' => $m->next_return_due?->format('Y-m-d'),
            'vat_bill_amount' => $m->vat_bill_amount,
            'vat_bill_due' => $m->vat_bill_due?->format('Y-m-d'),
            'latest_action_id' => $m->latest_action_id ? (string) $m->latest_action_id : '',
            'latest_action_date' => $m->latest_action_date?->format('Y-m-d'),
            'records_received' => $m->records_received?->format('Y-m-d'),
            'progress_note' => $m->progress_note,
            'vat_member_state_id' => $m->vat_member_state_id ? (string) $m->vat_member_state_id : '',
            'vat_number' => $m->vat_number,
            'vat_address' => $m->vat_address,
            'date_of_registration' => $m->date_of_registration?->format('Y-m-d'),
            'effective_date' => $m->effective_date?->format('Y-m-d'),
            'estimated_turnover' => $m->estimated_turnover,
            'applied_for_mtd' => $m->applied_for_mtd?->format('Y-m-d'),
            'mtd_ready' => $m->mtd_ready,
            'transfer_of_going_concern' => $m->transfer_of_going_concern,
            'involved_in_other_businesses' => $m->involved_in_other_businesses,
            'direct_debit' => $m->direct_debit,
            'standard_scheme' => $m->standard_scheme,
            'cash_accounting_scheme' => $m->cash_accounting_scheme,
            'retail_scheme' => $m->retail_scheme,
            'margin_scheme' => $m->margin_scheme,
            'flat_rate' => $m->flat_rate,
            'flat_rate_category_id' => $m->flat_rate_category_id ? (string) $m->flat_rate_category_id : '',
            'month_last_quarter_submitted' => $m->month_last_quarter_submitted,
            'box5_last_quarter_submitted' => $m->box5_last_quarter_submitted,
            'general_notes' => $m->general_notes,
        ];
    }

    private function mapPayeDetail(?PayeDetail $m): array
    {
        if (! $m) {
            return [];
        }

        return [
            'employers_reference' => $m->employers_reference,
            'accounts_office_reference' => $m->accounts_office_reference,
            'years_required' => $m->years_required,
            'paye_frequency_id' => $m->paye_frequency_id ? (string) $m->paye_frequency_id : '',
            'irregular_monthly_pay' => $m->irregular_monthly_pay,
            'nil_eps' => $m->nil_eps,
            'no_of_employees' => $m->no_of_employees,
            'salary_details' => $m->salary_details,
            'first_pay_date' => $m->first_pay_date?->format('Y-m-d'),
            'rti_deadline' => $m->rti_deadline?->format('Y-m-d'),
            'paye_scheme_ceased' => $m->paye_scheme_ceased?->format('Y-m-d'),
            'latest_action_id' => $m->latest_action_id ? (string) $m->latest_action_id : '',
            'latest_action_date' => $m->latest_action_date?->format('Y-m-d'),
            'records_received' => $m->records_received?->format('Y-m-d'),
            'progress_note' => $m->progress_note,
            'general_notes' => $m->general_notes,
        ];
    }

    private function mapCisDetail(?CisDetail $m): array
    {
        if (! $m) {
            return [];
        }

        return [
            'is_contractor' => $m->is_contractor,
            'is_subcontractor' => $m->is_subcontractor,
            'cis_date' => $m->cis_date?->format('Y-m-d'),
            'cis_deadline' => $m->cis_deadline?->format('Y-m-d'),
            'latest_action_id' => $m->latest_action_id ? (string) $m->latest_action_id : '',
            'latest_action_date' => $m->latest_action_date?->format('Y-m-d'),
            'records_received' => $m->records_received?->format('Y-m-d'),
            'progress_note' => $m->progress_note,
        ];
    }

    private function mapAutoEnrolment(?AutoEnrolment $m): array
    {
        if (! $m) {
            return [];
        }

        return [
            'latest_action_id' => $m->latest_action_id ? (string) $m->latest_action_id : '',
            'latest_action_date' => $m->latest_action_date?->format('Y-m-d'),
            'records_received' => $m->records_received?->format('Y-m-d'),
            'progress_note' => $m->progress_note,
            'staging_date' => $m->staging_date?->format('Y-m-d'),
            'postponement_date' => $m->postponement_date?->format('Y-m-d'),
            'pensions_regulator_opt_out_date' => $m->pensions_regulator_opt_out_date?->format('Y-m-d'),
            're_enrolment_date' => $m->re_enrolment_date?->format('Y-m-d'),
            'pension_provider' => $m->pension_provider,
            'pension_id' => $m->pension_id,
            'declaration_of_compliance_due' => $m->declaration_of_compliance_due?->format('Y-m-d'),
            'declaration_of_compliance_submission' => $m->declaration_of_compliance_submission?->format('Y-m-d'),
        ];
    }

    private function mapP11dDetail(?P11dDetail $m): array
    {
        if (! $m) {
            return [];
        }

        return [
            'next_return_due' => $m->next_return_due?->format('Y-m-d'),
            'latest_submitted' => $m->latest_submitted?->format('Y-m-d'),
            'latest_action_id' => $m->latest_action_id ? (string) $m->latest_action_id : '',
            'latest_action_date' => $m->latest_action_date?->format('Y-m-d'),
            'records_received' => $m->records_received?->format('Y-m-d'),
            'progress_note' => $m->progress_note,
        ];
    }

    private function mapRegistration(?ClientRegistration $m): array
    {
        if (! $m) {
            return [];
        }

        return [
            'terms_signed_fee_paid' => $m->terms_signed_fee_paid,
            'registration_fee' => $m->registration_fee,
            'letter_of_engagement_signed' => $m->letter_of_engagement_signed?->format('Y-m-d'),
            'money_laundering_complete' => $m->money_laundering_complete,
            'sixty_four_eight_registration' => $m->sixty_four_eight_registration?->format('Y-m-d'),
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyCombinedPricing(array $d): array
    {
        return [
            'annual_charge_enabled' => (bool) ($d['annual_charge_enabled'] ?? false),
            'annual_charge' => $d['annual_charge'] ?: null,
            'monthly_charge_enabled' => (bool) ($d['monthly_charge_enabled'] ?? false),
            'monthly_charge' => $d['monthly_charge'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyAccountsReturn(array $d): array
    {
        return [
            'accounts_period_end' => $d['accounts_period_end'] ?: null,
            'ch_year_end' => $d['ch_year_end'] ?: null,
            'hmrc_year_end' => $d['hmrc_year_end'] ?: null,
            'ch_accounts_next_due' => $d['ch_accounts_next_due'] ?: null,
            'ct600_due' => $d['ct600_due'] ?: null,
            'corporation_tax_amount_due' => $d['corporation_tax_amount_due'] ?: null,
            'tax_due_hmrc_year_end' => $d['tax_due_hmrc_year_end'] ?: null,
            'ct_payment_reference' => $d['ct_payment_reference'] ?: null,
            'tax_office_id' => $d['tax_office_id'] ?: null,
            'ch_email_reminder' => (bool) ($d['ch_email_reminder'] ?? false),
            'latest_action_id' => $d['latest_action_id'] ?: null,
            'latest_action_date' => $d['latest_action_date'] ?: null,
            'records_received' => $d['records_received'] ?: null,
            'progress_note' => $d['progress_note'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyConfirmationStatement(array $d): array
    {
        return [
            'statement_date' => $d['statement_date'] ?: null,
            'statement_due' => $d['statement_due'] ?: null,
            'latest_action_id' => $d['latest_action_id'] ?: null,
            'latest_action_date' => $d['latest_action_date'] ?: null,
            'records_received' => $d['records_received'] ?: null,
            'progress_note' => $d['progress_note'] ?: null,
            'officers' => $d['officers'] ?: null,
            'share_capital' => $d['share_capital'] ?: null,
            'shareholders' => $d['shareholders'] ?: null,
            'people_with_significant_control' => $d['people_with_significant_control'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyVatDetail(array $d): array
    {
        return [
            'vat_frequency_id' => $d['vat_frequency_id'] ?: null,
            'vat_period_end' => $d['vat_period_end'] ?: null,
            'next_return_due' => $d['next_return_due'] ?: null,
            'vat_bill_amount' => $d['vat_bill_amount'] ?: null,
            'vat_bill_due' => $d['vat_bill_due'] ?: null,
            'latest_action_id' => $d['latest_action_id'] ?: null,
            'latest_action_date' => $d['latest_action_date'] ?: null,
            'records_received' => $d['records_received'] ?: null,
            'progress_note' => $d['progress_note'] ?: null,
            'vat_member_state_id' => $d['vat_member_state_id'] ?: null,
            'vat_number' => $d['vat_number'] ?: null,
            'vat_address' => $d['vat_address'] ?: null,
            'date_of_registration' => $d['date_of_registration'] ?: null,
            'effective_date' => $d['effective_date'] ?: null,
            'estimated_turnover' => $d['estimated_turnover'] ?: null,
            'applied_for_mtd' => $d['applied_for_mtd'] ?: null,
            'mtd_ready' => (bool) ($d['mtd_ready'] ?? false),
            'transfer_of_going_concern' => (bool) ($d['transfer_of_going_concern'] ?? false),
            'involved_in_other_businesses' => (bool) ($d['involved_in_other_businesses'] ?? false),
            'direct_debit' => (bool) ($d['direct_debit'] ?? false),
            'standard_scheme' => (bool) ($d['standard_scheme'] ?? false),
            'cash_accounting_scheme' => (bool) ($d['cash_accounting_scheme'] ?? false),
            'retail_scheme' => (bool) ($d['retail_scheme'] ?? false),
            'margin_scheme' => (bool) ($d['margin_scheme'] ?? false),
            'flat_rate' => (bool) ($d['flat_rate'] ?? false),
            'flat_rate_category_id' => ($d['flat_rate'] ?? false) ? ($d['flat_rate_category_id'] ?: null) : null,
            'month_last_quarter_submitted' => $d['month_last_quarter_submitted'] ?: null,
            'box5_last_quarter_submitted' => $d['box5_last_quarter_submitted'] ?: null,
            'general_notes' => $d['general_notes'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyPayeDetail(array $d): array
    {
        return [
            'employers_reference' => $d['employers_reference'] ?: null,
            'accounts_office_reference' => $d['accounts_office_reference'] ?: null,
            'years_required' => $d['years_required'] ?: null,
            'paye_frequency_id' => $d['paye_frequency_id'] ?: null,
            'irregular_monthly_pay' => (bool) ($d['irregular_monthly_pay'] ?? false),
            'nil_eps' => (bool) ($d['nil_eps'] ?? false),
            'no_of_employees' => $d['no_of_employees'] ?: null,
            'salary_details' => $d['salary_details'] ?: null,
            'first_pay_date' => $d['first_pay_date'] ?: null,
            'rti_deadline' => $d['rti_deadline'] ?: null,
            'paye_scheme_ceased' => $d['paye_scheme_ceased'] ?: null,
            'latest_action_id' => $d['latest_action_id'] ?: null,
            'latest_action_date' => $d['latest_action_date'] ?: null,
            'records_received' => $d['records_received'] ?: null,
            'progress_note' => $d['progress_note'] ?: null,
            'general_notes' => $d['general_notes'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyCisDetail(array $d): array
    {
        return [
            'is_contractor' => (bool) ($d['is_contractor'] ?? false),
            'is_subcontractor' => (bool) ($d['is_subcontractor'] ?? false),
            'cis_date' => $d['cis_date'] ?: null,
            'cis_deadline' => $d['cis_deadline'] ?: null,
            'latest_action_id' => $d['latest_action_id'] ?: null,
            'latest_action_date' => $d['latest_action_date'] ?: null,
            'records_received' => $d['records_received'] ?: null,
            'progress_note' => $d['progress_note'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyAutoEnrolment(array $d): array
    {
        return [
            'latest_action_id' => $d['latest_action_id'] ?: null,
            'latest_action_date' => $d['latest_action_date'] ?: null,
            'records_received' => $d['records_received'] ?: null,
            'progress_note' => $d['progress_note'] ?: null,
            'staging_date' => $d['staging_date'] ?: null,
            'postponement_date' => $d['postponement_date'] ?: null,
            'pensions_regulator_opt_out_date' => $d['pensions_regulator_opt_out_date'] ?: null,
            're_enrolment_date' => $d['re_enrolment_date'] ?: null,
            'pension_provider' => $d['pension_provider'] ?: null,
            'pension_id' => $d['pension_id'] ?: null,
            'declaration_of_compliance_due' => $d['declaration_of_compliance_due'] ?: null,
            'declaration_of_compliance_submission' => $d['declaration_of_compliance_submission'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyP11dDetail(array $d): array
    {
        return [
            'next_return_due' => $d['next_return_due'] ?: null,
            'latest_submitted' => $d['latest_submitted'] ?: null,
            'latest_action_id' => $d['latest_action_id'] ?: null,
            'latest_action_date' => $d['latest_action_date'] ?: null,
            'records_received' => $d['records_received'] ?: null,
            'progress_note' => $d['progress_note'] ?: null,
        ];
    }

    /**
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function onlyRegistration(array $d): array
    {
        return [
            'terms_signed_fee_paid' => (bool) ($d['terms_signed_fee_paid'] ?? false),
            'registration_fee' => ($d['terms_signed_fee_paid'] ?? false) ? ($d['registration_fee'] ?: null) : null,
            'letter_of_engagement_signed' => $d['letter_of_engagement_signed'] ?: null,
            'money_laundering_complete' => (bool) ($d['money_laundering_complete'] ?? false),
            'sixty_four_eight_registration' => $d['sixty_four_eight_registration'] ?: null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(int $tenantId): array
    {
        $existsContact = Rule::exists('contacts', 'id')->where('tenant_id', $tenantId);

        return [
            'combined_pricing' => ['nullable', 'array'],
            'combined_pricing.annual_charge_enabled' => ['boolean'],
            'combined_pricing.annual_charge' => ['nullable', 'numeric', 'min:0'],
            'combined_pricing.monthly_charge_enabled' => ['boolean'],
            'combined_pricing.monthly_charge' => ['nullable', 'numeric', 'min:0'],

            'services' => ['nullable', 'array'],
            'services.*.service_id' => ['required', 'exists:services,id'],
            'services.*.is_enabled' => ['boolean'],
            'services.*.fee' => ['nullable', 'numeric', 'min:0'],

            'main_contact' => ['nullable', 'array'],
            'main_contact.id' => ['nullable', 'integer', $existsContact],
            'main_contact.title_id' => ['nullable', 'exists:lkp_titles,id'],
            'main_contact.first_name' => ['nullable', 'string', 'max:100'],
            'main_contact.middle_name' => ['nullable', 'string', 'max:100'],
            'main_contact.last_name' => ['nullable', 'string', 'max:100'],
            'main_contact.preferred_name' => ['nullable', 'string', 'max:100'],
            'main_contact.date_of_birth' => ['nullable', 'date'],
            'main_contact.email' => ['nullable', 'email', 'max:255'],
            'main_contact.portal_login_email' => ['nullable', 'email', 'max:255'],
            'main_contact.postal_address' => ['nullable', 'string', 'max:5000'],
            'main_contact.telephone_number' => ['nullable', 'string', 'max:30'],
            'main_contact.mobile_number' => ['nullable', 'string', 'max:30'],
            'main_contact.ni_number' => ['nullable', 'string', 'max:15'],
            'main_contact.personal_utr' => ['nullable', 'string', 'max:20'],
            'main_contact.create_self_assessment' => ['boolean'],
            'main_contact.self_assessment_fee' => ['nullable', 'numeric', 'min:0'],
            'main_contact.client_does_own_sa' => ['boolean'],
            'main_contact.deceased_date' => ['nullable', 'date'],
            'main_contact.previous_address' => ['nullable', 'string', 'max:5000'],
            'main_contact.companies_house_personal_code' => ['nullable', 'string', 'max:20'],
            'main_contact.terms_signed_date' => ['nullable', 'date'],
            'main_contact.photo_id_verified' => ['boolean'],
            'main_contact.address_verified' => ['boolean'],
            'main_contact.marital_status_id' => ['nullable', 'exists:lkp_marital_statuses,id'],
            'main_contact.nationality_id' => ['nullable', 'exists:lkp_nationalities,id'],
            'main_contact.language_id' => ['nullable', 'exists:lkp_languages,id'],
            'main_contact.aml_check_started' => ['boolean'],
            'main_contact.aml_check_date' => ['nullable', 'date'],
            'main_contact.id_check_started' => ['boolean'],
            'main_contact.id_check_date' => ['nullable', 'date'],

            'secondary_contact' => ['nullable', 'array'],
            'secondary_contact.id' => ['nullable', 'integer', $existsContact],
            'secondary_contact.title_id' => ['nullable', 'exists:lkp_titles,id'],
            'secondary_contact.first_name' => ['nullable', 'string', 'max:100'],
            'secondary_contact.middle_name' => ['nullable', 'string', 'max:100'],
            'secondary_contact.last_name' => ['nullable', 'string', 'max:100'],
            'secondary_contact.preferred_name' => ['nullable', 'string', 'max:100'],
            'secondary_contact.date_of_birth' => ['nullable', 'date'],
            'secondary_contact.deceased_date' => ['nullable', 'date'],
            'secondary_contact.email' => ['nullable', 'email', 'max:255'],
            'secondary_contact.portal_login_email' => ['nullable', 'email', 'max:255'],
            'secondary_contact.postal_address' => ['nullable', 'string', 'max:5000'],
            'secondary_contact.previous_address' => ['nullable', 'string', 'max:5000'],
            'secondary_contact.telephone_number' => ['nullable', 'string', 'max:30'],
            'secondary_contact.mobile_number' => ['nullable', 'string', 'max:30'],
            'secondary_contact.ni_number' => ['nullable', 'string', 'max:15'],
            'secondary_contact.personal_utr' => ['nullable', 'string', 'max:20'],
            'secondary_contact.companies_house_personal_code' => ['nullable', 'string', 'max:20'],
            'secondary_contact.terms_signed_date' => ['nullable', 'date'],
            'secondary_contact.photo_id_verified' => ['boolean'],
            'secondary_contact.address_verified' => ['boolean'],
            'secondary_contact.marital_status_id' => ['nullable', 'exists:lkp_marital_statuses,id'],
            'secondary_contact.nationality_id' => ['nullable', 'exists:lkp_nationalities,id'],
            'secondary_contact.language_id' => ['nullable', 'exists:lkp_languages,id'],
            'secondary_contact.aml_check_started' => ['boolean'],
            'secondary_contact.aml_check_date' => ['nullable', 'date'],
            'secondary_contact.id_check_started' => ['boolean'],
            'secondary_contact.id_check_date' => ['nullable', 'date'],

            'accounts_returns' => ['nullable', 'array'],
            'accounts_returns.accounts_period_end' => ['nullable', 'date'],
            'accounts_returns.ch_year_end' => ['nullable', 'date'],
            'accounts_returns.hmrc_year_end' => ['nullable', 'date'],
            'accounts_returns.ch_accounts_next_due' => ['nullable', 'date'],
            'accounts_returns.ct600_due' => ['nullable', 'date'],
            'accounts_returns.corporation_tax_amount_due' => ['nullable', 'numeric', 'min:0'],
            'accounts_returns.tax_due_hmrc_year_end' => ['nullable', 'date'],
            'accounts_returns.ct_payment_reference' => ['nullable', 'string', 'max:50'],
            'accounts_returns.tax_office_id' => ['nullable', 'exists:lkp_tax_offices,id'],
            'accounts_returns.ch_email_reminder' => ['boolean'],
            'accounts_returns.latest_action_id' => ['nullable', 'exists:lkp_action_statuses,id'],
            'accounts_returns.latest_action_date' => ['nullable', 'date'],
            'accounts_returns.records_received' => ['nullable', 'date'],
            'accounts_returns.progress_note' => ['nullable', 'string'],

            'confirmation_statement' => ['nullable', 'array'],
            'confirmation_statement.statement_date' => ['nullable', 'date'],
            'confirmation_statement.statement_due' => ['nullable', 'date'],
            'confirmation_statement.latest_action_id' => ['nullable', 'exists:lkp_action_statuses,id'],
            'confirmation_statement.latest_action_date' => ['nullable', 'date'],
            'confirmation_statement.records_received' => ['nullable', 'date'],
            'confirmation_statement.progress_note' => ['nullable', 'string'],
            'confirmation_statement.officers' => ['nullable', 'string'],
            'confirmation_statement.share_capital' => ['nullable', 'string'],
            'confirmation_statement.shareholders' => ['nullable', 'string'],
            'confirmation_statement.people_with_significant_control' => ['nullable', 'string'],

            'vat' => ['nullable', 'array'],
            'vat.vat_frequency_id' => ['nullable', 'exists:lkp_vat_frequencies,id'],
            'vat.vat_period_end' => ['nullable', 'date'],
            'vat.next_return_due' => ['nullable', 'date'],
            'vat.vat_bill_amount' => ['nullable', 'numeric', 'min:0'],
            'vat.vat_bill_due' => ['nullable', 'date'],
            'vat.latest_action_id' => ['nullable', 'exists:lkp_action_statuses,id'],
            'vat.latest_action_date' => ['nullable', 'date'],
            'vat.records_received' => ['nullable', 'date'],
            'vat.progress_note' => ['nullable', 'string'],
            'vat.vat_member_state_id' => ['nullable', 'exists:lkp_vat_member_states,id'],
            'vat.vat_number' => ['nullable', 'string', 'max:20'],
            'vat.vat_address' => ['nullable', 'string'],
            'vat.date_of_registration' => ['nullable', 'date'],
            'vat.effective_date' => ['nullable', 'date'],
            'vat.estimated_turnover' => ['nullable', 'numeric', 'min:0'],
            'vat.applied_for_mtd' => ['nullable', 'date'],
            'vat.mtd_ready' => ['boolean'],
            'vat.transfer_of_going_concern' => ['boolean'],
            'vat.involved_in_other_businesses' => ['boolean'],
            'vat.direct_debit' => ['boolean'],
            'vat.standard_scheme' => ['boolean'],
            'vat.cash_accounting_scheme' => ['boolean'],
            'vat.retail_scheme' => ['boolean'],
            'vat.margin_scheme' => ['boolean'],
            'vat.flat_rate' => ['boolean'],
            'vat.flat_rate_category_id' => ['nullable', 'exists:lkp_flat_rate_categories,id'],
            'vat.month_last_quarter_submitted' => ['nullable', 'integer', 'min:1', 'max:12'],
            'vat.box5_last_quarter_submitted' => ['nullable', 'numeric', 'min:0'],
            'vat.general_notes' => ['nullable', 'string'],

            'paye' => ['nullable', 'array'],
            'paye.employers_reference' => ['nullable', 'string', 'max:50'],
            'paye.accounts_office_reference' => ['nullable', 'string', 'max:50'],
            'paye.years_required' => ['nullable', 'string', 'max:20'],
            'paye.paye_frequency_id' => ['nullable', 'exists:lkp_paye_frequencies,id'],
            'paye.irregular_monthly_pay' => ['boolean'],
            'paye.nil_eps' => ['boolean'],
            'paye.no_of_employees' => ['nullable', 'integer', 'min:0'],
            'paye.salary_details' => ['nullable', 'string'],
            'paye.first_pay_date' => ['nullable', 'date'],
            'paye.rti_deadline' => ['nullable', 'date'],
            'paye.paye_scheme_ceased' => ['nullable', 'date'],
            'paye.latest_action_id' => ['nullable', 'exists:lkp_action_statuses,id'],
            'paye.latest_action_date' => ['nullable', 'date'],
            'paye.records_received' => ['nullable', 'date'],
            'paye.progress_note' => ['nullable', 'string'],
            'paye.general_notes' => ['nullable', 'string'],

            'cis' => ['nullable', 'array'],
            'cis.is_contractor' => ['boolean'],
            'cis.is_subcontractor' => ['boolean'],
            'cis.cis_date' => ['nullable', 'date'],
            'cis.cis_deadline' => ['nullable', 'date'],
            'cis.latest_action_id' => ['nullable', 'exists:lkp_action_statuses,id'],
            'cis.latest_action_date' => ['nullable', 'date'],
            'cis.records_received' => ['nullable', 'date'],
            'cis.progress_note' => ['nullable', 'string'],

            'auto_enrolment' => ['nullable', 'array'],
            'auto_enrolment.latest_action_id' => ['nullable', 'exists:lkp_action_statuses,id'],
            'auto_enrolment.latest_action_date' => ['nullable', 'date'],
            'auto_enrolment.records_received' => ['nullable', 'date'],
            'auto_enrolment.progress_note' => ['nullable', 'string'],
            'auto_enrolment.staging_date' => ['nullable', 'date'],
            'auto_enrolment.postponement_date' => ['nullable', 'date'],
            'auto_enrolment.pensions_regulator_opt_out_date' => ['nullable', 'date'],
            'auto_enrolment.re_enrolment_date' => ['nullable', 'date'],
            'auto_enrolment.pension_provider' => ['nullable', 'string', 'max:150'],
            'auto_enrolment.pension_id' => ['nullable', 'string', 'max:50'],
            'auto_enrolment.declaration_of_compliance_due' => ['nullable', 'date'],
            'auto_enrolment.declaration_of_compliance_submission' => ['nullable', 'date'],

            'p11d' => ['nullable', 'array'],
            'p11d.next_return_due' => ['nullable', 'date'],
            'p11d.latest_submitted' => ['nullable', 'date'],
            'p11d.latest_action_id' => ['nullable', 'exists:lkp_action_statuses,id'],
            'p11d.latest_action_date' => ['nullable', 'date'],
            'p11d.records_received' => ['nullable', 'date'],
            'p11d.progress_note' => ['nullable', 'string'],

            'registration' => ['nullable', 'array'],
            'registration.terms_signed_fee_paid' => ['boolean'],
            'registration.registration_fee' => ['nullable', 'numeric', 'min:0'],
            'registration.letter_of_engagement_signed' => ['nullable', 'date'],
            'registration.money_laundering_complete' => ['boolean'],
            'registration.sixty_four_eight_registration' => ['nullable', 'date'],
        ];
    }
}
