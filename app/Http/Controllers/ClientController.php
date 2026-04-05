<?php

namespace App\Http\Controllers;

use App\Models\ActionStatus;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\CompanyDetail;
use App\Models\CompanyStatus;
use App\Models\ContactTitle;
use App\Models\FlatRateCategory;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\Nationality;
use App\Models\PayeFrequency;
use App\Models\SicCode;
use App\Models\TaxOffice;
use App\Models\User;
use App\Models\VatFrequency;
use App\Models\VatMemberState;
use App\Services\ClientExtendedDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(
        private ClientExtendedDataService $clientExtendedData,
    ) {
        $this->authorizeResource(Client::class, 'client');
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $query = Client::query()
            ->forTenant($tenantId)
            ->with(['clientType', 'partner', 'manager', 'companyDetail']);

        if ($user->role === User::ROLE_STAFF) {
            $query->where('created_by_id', $user->id);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('internal_reference', 'like', $like)
                    ->orWhereHas('companyDetail', function ($q) use ($like) {
                        $q->where('company_number', 'like', $like)
                            ->orWhere('company_utr', 'like', $like);
                    });
            });
        }

        if ($request->filled('client_type_id')) {
            $query->where('client_type_id', $request->integer('client_type_id'));
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->integer('partner_id'));
        }

        if ($request->filled('manager_id')) {
            $query->where('manager_id', $request->integer('manager_id'));
        }

        $status = $request->input('status', 'active');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $clients = $query->orderBy('name')->paginate(15)->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
                'client_type_id' => $request->input('client_type_id'),
                'partner_id' => $request->input('partner_id'),
                'manager_id' => $request->input('manager_id'),
                'status' => $status,
            ],
            'clientTypes' => ClientType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'partnerOptions' => $this->partnerUserOptions($tenantId),
            'managerOptions' => $this->managerUserOptions($tenantId),
        ]);
    }

    public function create(Request $request): Response
    {
        $tenantId = $request->user()->tenant_id;

        return Inertia::render('Clients/Create', [
            'clientTypes' => ClientType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'partnerOptions' => $this->partnerUserOptions($tenantId),
            'managerOptions' => $this->managerUserOptions($tenantId),
            'companyStatuses' => CompanyStatus::query()->orderBy('name')->get(['id', 'name']),
            'sicCodes' => SicCode::query()->orderBy('code')->limit(500)->get(['id', 'code', 'description']),
            'company' => $this->emptyCompanyPayload(),
            'extended' => $this->clientExtendedData->emptyExtendedFormPayload(),
            'extendedLookups' => $this->extendedFormLookups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $validated = $this->validateClientPayload($request, $tenantId);

        $clientData = $validated['client'];
        $companyData = $validated['company'];

        $this->applyAssignmentRules($user, $clientData, null);

        if (empty($clientData['internal_reference'])) {
            $clientData['internal_reference'] = Client::generateInternalReference($tenantId);
        }

        $clientData['tenant_id'] = $tenantId;
        $clientData['created_by_id'] = $user->id;
        $clientData['is_active'] = true;
        if (! array_key_exists('is_prospect', $clientData)) {
            $clientData['is_prospect'] = true;
        }

        $client = DB::transaction(function () use ($request, $clientData, $companyData) {
            $created = Client::query()->create($clientData);
            CompanyDetail::query()->create(array_merge($companyData, ['client_id' => $created->id]));
            $this->clientExtendedData->bootstrapForNewClient($created);
            $this->clientExtendedData->syncFromRequest($request, $created);

            return $created;
        });

        if ($request->boolean('onboarding_workflow')) {
            return redirect()->route('clients.show', $client)
                ->with('success', 'Client created. Continue onboarding using tasks and compliance sections.')
                ->with('onboarding', true);
        }

        return redirect()->route('clients.index')->with('success', 'Client created.');
    }

    public function show(Request $request, Client $client): Response
    {
        $client->load(['clientType', 'partner', 'manager', 'createdBy', 'companyDetail.companyStatus', 'companyDetail.sicCode']);

        $this->clientExtendedData->ensureBootstrapped($client);
        $extended = $this->clientExtendedData->buildFormPayload($client);

        return Inertia::render('Clients/Show', [
            'client' => $client,
            'extended' => $extended,
            'canDelete' => $request->user()->can('delete', $client),
        ]);
    }

    public function edit(Request $request, Client $client): Response
    {
        $tenantId = $request->user()->tenant_id;

        $client->load(['companyDetail']);

        $this->clientExtendedData->ensureBootstrapped($client);
        $extended = $this->clientExtendedData->buildFormPayload($client);

        return Inertia::render('Clients/Edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'client_type_id' => $client->client_type_id,
                'internal_reference' => $client->internal_reference,
                'partner_id' => $client->partner_id,
                'manager_id' => $client->manager_id,
                'credit_check_completed' => $client->credit_check_completed,
                'credit_check_date' => $client->credit_check_date?->format('Y-m-d'),
                'income_details' => $client->income_details,
                'previous_accountant_name' => $client->previous_accountant_name,
                'previous_accountant_details' => $client->previous_accountant_details,
                'other_details' => $client->other_details,
                'is_prospect' => $client->is_prospect,
            ],
            'clientTypes' => ClientType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'partnerOptions' => $this->partnerUserOptions($tenantId),
            'managerOptions' => $this->managerUserOptions($tenantId),
            'companyStatuses' => CompanyStatus::query()->orderBy('name')->get(['id', 'name']),
            'sicCodes' => SicCode::query()->orderBy('code')->limit(500)->get(['id', 'code', 'description']),
            'company' => $this->companyPayloadFromModel($client->companyDetail),
            'extended' => $extended,
            'extendedLookups' => $this->extendedFormLookups(),
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        $validated = $this->validateClientPayload($request, $tenantId, $client->id);

        $clientData = $validated['client'];
        $companyData = $validated['company'];

        $this->applyAssignmentRules($user, $clientData, $client);

        $this->clientExtendedData->ensureBootstrapped($client);

        DB::transaction(function () use ($request, $client, $clientData, $companyData) {
            $client->update($clientData);
            $client->companyDetail()->updateOrCreate(
                ['client_id' => $client->id],
                $companyData
            );
            $this->clientExtendedData->syncFromRequest($request, $client);
        });

        return redirect()->route('clients.show', $client)->with('success', 'Client updated.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $client->update(['is_active' => false]);

        return redirect()->route('clients.index')->with('success', 'Client deactivated.');
    }

    /**
     * @return array{client: array<string, mixed>, company: array<string, mixed>}
     */
    private function validateClientPayload(Request $request, int $tenantId, ?int $clientId = null): array
    {
        $userRule = Rule::exists('users', 'id')->where(fn ($q) => $q->where('tenant_id', $tenantId));

        $internalRefRule = Rule::unique('clients', 'internal_reference');
        if ($clientId !== null) {
            $internalRefRule = $internalRefRule->ignore($clientId);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'client_type_id' => ['required', 'exists:lkp_client_types,id'],
            'internal_reference' => ['nullable', 'string', 'max:50', $internalRefRule],
            'partner_id' => ['nullable', 'integer', $userRule],
            'manager_id' => ['nullable', 'integer', $userRule],
            'credit_check_date' => ['nullable', 'date'],
            'income_details' => ['nullable', 'string', 'max:65535'],
            'previous_accountant_name' => ['nullable', 'string', 'max:255'],
            'previous_accountant_details' => ['nullable', 'string', 'max:65535'],
            'other_details' => ['nullable', 'string', 'max:65535'],
            'is_prospect' => ['sometimes', 'boolean'],
            'company' => ['nullable', 'array'],
            'company.company_number' => ['nullable', 'string', 'max:20'],
            'company.company_status_id' => ['nullable', 'exists:lkp_company_statuses,id'],
            'company.incorporation_date' => ['nullable', 'date'],
            'company.trading_as' => ['nullable', 'string', 'max:255'],
            'company.registered_address' => ['nullable', 'string', 'max:5000'],
            'company.postal_address' => ['nullable', 'string', 'max:5000'],
            'company.invoice_address_type' => ['nullable', 'string', Rule::in(['registered', 'postal', 'custom'])],
            'company.invoice_address_custom' => ['nullable', 'string', 'max:5000'],
            'company.primary_email' => ['nullable', 'email', 'max:255'],
            'company.email_domain' => ['nullable', 'string', 'max:255'],
            'company.telephone' => ['nullable', 'string', 'max:30'],
            'company.turnover' => ['nullable', 'numeric', 'min:0'],
            'company.date_of_trading' => ['nullable', 'date'],
            'company.sic_code_id' => ['nullable', 'exists:lkp_sic_codes,id'],
            'company.nature_of_business' => ['nullable', 'string', 'max:255'],
            'company.corporation_tax_office' => ['nullable', 'string', 'max:150'],
            'company.company_utr' => ['nullable', 'string', 'max:20'],
            'company.companies_house_auth_code' => ['nullable', 'string', 'max:20'],
        ]);

        $company = array_merge($this->emptyCompanyPayload(), $validated['company'] ?? []);

        $creditDone = $request->boolean('credit_check_completed');

        $client = [
            'name' => $validated['name'],
            'client_type_id' => $validated['client_type_id'],
            'internal_reference' => $validated['internal_reference'] ?? null,
            'partner_id' => $validated['partner_id'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'credit_check_completed' => $creditDone,
            'credit_check_date' => $creditDone ? ($validated['credit_check_date'] ?? null) : null,
            'income_details' => $validated['income_details'] ?? null,
            'previous_accountant_name' => $validated['previous_accountant_name'] ?? null,
            'previous_accountant_details' => $validated['previous_accountant_details'] ?? null,
            'other_details' => $validated['other_details'] ?? null,
            'is_prospect' => (bool) ($validated['is_prospect'] ?? true),
        ];

        return ['client' => $client, 'company' => $company];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyAssignmentRules(User $user, array &$data, ?Client $existing): void
    {
        if ($user->role === User::ROLE_STAFF || ($user->role === User::ROLE_MANAGER && ! $user->isTenantAdmin())) {
            if ($existing) {
                $data['partner_id'] = $existing->partner_id;
                $data['manager_id'] = $existing->manager_id;
            } else {
                $data['partner_id'] = null;
                $data['manager_id'] = null;
            }
        }

        if ($user->role === User::ROLE_PARTNER && ! $user->isTenantAdmin()) {
            $pid = $data['partner_id'] ?? null;
            if ($pid !== null && (int) $pid !== (int) $user->id) {
                throw ValidationException::withMessages([
                    'partner_id' => 'You can only assign yourself as partner.',
                ]);
            }
            if ($existing) {
                $data['manager_id'] = $existing->manager_id;
            } else {
                $data['manager_id'] = null;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyCompanyPayload(): array
    {
        return [
            'company_number' => null,
            'company_status_id' => null,
            'incorporation_date' => null,
            'trading_as' => null,
            'registered_address' => null,
            'postal_address' => null,
            'invoice_address_type' => 'postal',
            'invoice_address_custom' => null,
            'primary_email' => null,
            'email_domain' => null,
            'telephone' => null,
            'turnover' => null,
            'date_of_trading' => null,
            'sic_code_id' => null,
            'nature_of_business' => null,
            'corporation_tax_office' => null,
            'company_utr' => null,
            'companies_house_auth_code' => null,
        ];
    }

    private function companyPayloadFromModel(?CompanyDetail $detail): array
    {
        if (! $detail) {
            return $this->emptyCompanyPayload();
        }

        return [
            'company_number' => $detail->company_number,
            'company_status_id' => $detail->company_status_id ? (string) $detail->company_status_id : '',
            'incorporation_date' => $detail->incorporation_date?->format('Y-m-d'),
            'trading_as' => $detail->trading_as,
            'registered_address' => $detail->registered_address,
            'postal_address' => $detail->postal_address,
            'invoice_address_type' => $detail->invoice_address_type ?? 'postal',
            'invoice_address_custom' => $detail->invoice_address_custom,
            'primary_email' => $detail->primary_email,
            'email_domain' => $detail->email_domain,
            'telephone' => $detail->telephone,
            'turnover' => $detail->turnover,
            'date_of_trading' => $detail->date_of_trading?->format('Y-m-d'),
            'sic_code_id' => $detail->sic_code_id ? (string) $detail->sic_code_id : '',
            'nature_of_business' => $detail->nature_of_business,
            'corporation_tax_office' => $detail->corporation_tax_office,
            'company_utr' => $detail->company_utr,
            'companies_house_auth_code' => $detail->companies_house_auth_code,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function extendedFormLookups(): array
    {
        return [
            'titles' => ContactTitle::query()->orderBy('name')->get(['id', 'name']),
            'action_statuses' => ActionStatus::query()->orderBy('name')->get(['id', 'name', 'category']),
            'tax_offices' => TaxOffice::query()->orderBy('name')->get(['id', 'name']),
            'vat_frequencies' => VatFrequency::query()->orderBy('name')->get(['id', 'name']),
            'paye_frequencies' => PayeFrequency::query()->orderBy('name')->get(['id', 'name']),
            'flat_rate_categories' => FlatRateCategory::query()->orderBy('name')->get(['id', 'name', 'rate']),
            'vat_member_states' => VatMemberState::query()->orderBy('name')->get(['id', 'name', 'code']),
            'marital_statuses' => MaritalStatus::query()->orderBy('name')->get(['id', 'name']),
            'nationalities' => Nationality::query()->orderBy('name')->get(['id', 'name']),
            'languages' => Language::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function partnerUserOptions(int $tenantId): \Illuminate\Support\Collection
    {
        return User::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('role', [User::ROLE_PARTNER, User::ROLE_TENANT_ADMIN])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }

    private function managerUserOptions(int $tenantId): \Illuminate\Support\Collection
    {
        return User::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('role', [User::ROLE_MANAGER, User::ROLE_TENANT_ADMIN])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }
}
