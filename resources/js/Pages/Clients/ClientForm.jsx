import { Link, useForm, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import ClientExtendedSections from './ClientExtendedSections';

function emptyCompany() {
    return {
        company_number: '',
        company_status_id: '',
        incorporation_date: '',
        trading_as: '',
        registered_address: '',
        postal_address: '',
        invoice_address_type: 'postal',
        invoice_address_custom: '',
        primary_email: '',
        email_domain: '',
        telephone: '',
        turnover: '',
        date_of_trading: '',
        sic_code_id: '',
        nature_of_business: '',
        corporation_tax_office: '',
        company_utr: '',
        companies_house_auth_code: '',
    };
}

export default function ClientForm({
    mode,
    client,
    clientTypes,
    partnerOptions,
    managerOptions,
    companyStatuses,
    sicCodes,
    company: initialCompany,
    extended: initialExtended,
    extendedLookups,
}) {
    const { auth } = usePage().props;
    const role = auth.user.role;

    const hideAssignments = role === 'staff' || role === 'manager';
    const showPartnerSelect = !hideAssignments;
    const showManagerSelect = !hideAssignments && role !== 'partner';

    const partnerChoices = useMemo(() => {
        if (role === 'partner') {
            return partnerOptions.filter((p) => p.id === auth.user.id);
        }
        return partnerOptions;
    }, [partnerOptions, role, auth.user.id]);

    const form = useForm({
        name: client?.name ?? '',
        client_type_id:
            client?.client_type_id !== undefined && client?.client_type_id !== null
                ? String(client.client_type_id)
                : '',
        internal_reference: client?.internal_reference ?? '',
        partner_id:
            client?.partner_id !== undefined && client?.partner_id !== null
                ? String(client.partner_id)
                : '',
        manager_id:
            client?.manager_id !== undefined && client?.manager_id !== null
                ? String(client.manager_id)
                : '',
        credit_check_completed: client?.credit_check_completed ?? false,
        credit_check_date: client?.credit_check_date
            ? String(client.credit_check_date).slice(0, 10)
            : '',
        income_details: client?.income_details ?? '',
        previous_accountant_name: client?.previous_accountant_name ?? '',
        previous_accountant_details: client?.previous_accountant_details ?? '',
        other_details: client?.other_details ?? '',
        is_prospect: client?.is_prospect !== undefined ? !!client.is_prospect : true,
        onboarding_workflow: false,
        company: { ...emptyCompany(), ...initialCompany },
        ...(initialExtended ?? {}),
    });

    const [chMessage, setChMessage] = useState(null);
    const [chLoading, setChLoading] = useState(false);

    const c = form.data.company;

    const submit = (e, onboardingWorkflow = false) => {
        e.preventDefault();
        const opts = { preserveScroll: true };
        form.transform((data) => ({ ...data, onboarding_workflow: !!onboardingWorkflow }));
        if (mode === 'create') {
            form.post('/clients', opts);
        } else {
            form.put(`/clients/${client.id}`, opts);
        }
    };

    const autofillCompaniesHouse = async () => {
        setChMessage(null);
        const num = String(c.company_number ?? '').trim();
        if (!num) {
            setChMessage('Enter a company number first.');
            return;
        }
        setChLoading(true);
        try {
            const { data } = await window.axios.post('/lookup/companies-house', {
                company_number: num,
            });
            if (data.suggested_name && !String(form.data.name ?? '').trim()) {
                form.setData('name', data.suggested_name);
            }
            if (data.company) {
                form.setData('company', { ...form.data.company, ...data.company });
            }
            setChMessage('Loaded from Companies House.');
        } catch (err) {
            setChMessage(err.response?.data?.message ?? 'Companies House lookup failed.');
        } finally {
            setChLoading(false);
        }
    };

    const setCompany = (key, value) => {
        form.setData('company', { ...form.data.company, [key]: value });
    };

    return (
        <form
            onSubmit={(e) => submit(e, false)}
            className="space-y-8"
        >
            <details open className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Required information
                </summary>
                <div className="space-y-5 px-6 py-6">
                    <div>
                        <label htmlFor="name" className="label-field">
                            Client name <span className="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            type="text"
                            className="input-field"
                            value={form.data.name}
                            onChange={(e) => form.setData('name', e.target.value)}
                            required
                        />
                        {form.errors.name && (
                            <p className="mt-1 text-sm text-red-600">{form.errors.name}</p>
                        )}
                    </div>
                    <div>
                        <label htmlFor="client_type_id" className="label-field">
                            Client type <span className="text-red-500">*</span>
                        </label>
                        <select
                            id="client_type_id"
                            className="input-field"
                            value={form.data.client_type_id}
                            onChange={(e) => form.setData('client_type_id', e.target.value)}
                            required
                        >
                            <option value="">Select type…</option>
                            {clientTypes.map((t) => (
                                <option key={t.id} value={t.id}>
                                    {t.name}
                                </option>
                            ))}
                        </select>
                        {form.errors.client_type_id && (
                            <p className="mt-1 text-sm text-red-600">{form.errors.client_type_id}</p>
                        )}
                    </div>
                    {showPartnerSelect && (
                        <div>
                            <label htmlFor="partner_id" className="label-field">
                                Partner
                            </label>
                            <select
                                id="partner_id"
                                className="input-field"
                                value={form.data.partner_id}
                                onChange={(e) => form.setData('partner_id', e.target.value)}
                            >
                                <option value="">—</option>
                                {partnerChoices.map((p) => (
                                    <option key={p.id} value={p.id}>
                                        {p.name}
                                    </option>
                                ))}
                            </select>
                            {form.errors.partner_id && (
                                <p className="mt-1 text-sm text-red-600">{form.errors.partner_id}</p>
                            )}
                        </div>
                    )}
                    {showManagerSelect && (
                        <div>
                            <label htmlFor="manager_id" className="label-field">
                                Manager
                            </label>
                            <select
                                id="manager_id"
                                className="input-field"
                                value={form.data.manager_id}
                                onChange={(e) => form.setData('manager_id', e.target.value)}
                            >
                                <option value="">—</option>
                                {managerOptions.map((m) => (
                                    <option key={m.id} value={m.id}>
                                        {m.name}
                                    </option>
                                ))}
                            </select>
                            {form.errors.manager_id && (
                                <p className="mt-1 text-sm text-red-600">{form.errors.manager_id}</p>
                            )}
                        </div>
                    )}
                    <div className="rounded-lg bg-slate-50 px-4 py-3 ring-1 ring-slate-200/80">
                        <label className="flex items-start gap-3">
                            <input
                                type="checkbox"
                                className="mt-1 rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                checked={!form.data.is_prospect}
                                onChange={(e) => form.setData('is_prospect', !e.target.checked)}
                            />
                            <span>
                                <span className="text-sm font-medium text-slate-900">
                                    Confirmed client — enable automated tasks
                                </span>
                                <span className="mt-1 block text-xs text-slate-600">
                                    Bright Manager does not create tasks for prospects. Tick this when the
                                    engagement is confirmed so tasks can sync from enabled services.
                                </span>
                            </span>
                        </label>
                    </div>
                    <div className="flex flex-wrap items-center gap-6">
                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                checked={form.data.credit_check_completed}
                                onChange={(e) =>
                                    form.setData('credit_check_completed', e.target.checked)
                                }
                            />
                            <span className="text-sm text-slate-700">Credit check completed</span>
                        </label>
                        {form.data.credit_check_completed && (
                            <div className="min-w-[10rem]">
                                <label htmlFor="credit_check_date" className="label-field">
                                    Credit check date
                                </label>
                                <input
                                    id="credit_check_date"
                                    type="date"
                                    className="input-field"
                                    value={form.data.credit_check_date || ''}
                                    onChange={(e) => form.setData('credit_check_date', e.target.value)}
                                />
                            </div>
                        )}
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Internal reference
                </summary>
                <div className="px-6 py-6">
                    <label htmlFor="internal_reference" className="label-field">
                        Internal reference
                    </label>
                    <input
                        id="internal_reference"
                        type="text"
                        className="input-field"
                        value={form.data.internal_reference}
                        onChange={(e) => form.setData('internal_reference', e.target.value)}
                        placeholder={mode === 'create' ? 'Leave blank to auto-generate' : ''}
                    />
                    <p className="mt-1 text-xs text-slate-500">
                        Unique code for your practice. Leave empty on create to generate automatically.
                    </p>
                    {form.errors.internal_reference && (
                        <p className="mt-1 text-sm text-red-600">{form.errors.internal_reference}</p>
                    )}
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Company details
                </summary>
                <div className="grid gap-5 px-6 py-6 sm:grid-cols-2">
                    <div className="sm:col-span-2 flex flex-wrap items-end gap-3">
                        <div className="min-w-[12rem] flex-1">
                            <label className="label-field">Company number</label>
                            <input
                                type="text"
                                className="input-field"
                                value={c.company_number}
                                onChange={(e) => setCompany('company_number', e.target.value)}
                                placeholder="e.g. 12345678"
                            />
                        </div>
                        <button
                            type="button"
                            className="btn-secondary shrink-0"
                            disabled={chLoading}
                            onClick={autofillCompaniesHouse}
                        >
                            {chLoading ? 'Looking up…' : 'Autofill from Companies House'}
                        </button>
                    </div>
                    {chMessage && (
                        <p className="sm:col-span-2 text-sm text-slate-600">{chMessage}</p>
                    )}
                    <div>
                        <label className="label-field">Company status</label>
                        <select
                            className="input-field"
                            value={c.company_status_id}
                            onChange={(e) => setCompany('company_status_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {companyStatuses.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.name}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">Incorporation date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={c.incorporation_date || ''}
                            onChange={(e) => setCompany('incorporation_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Trading as</label>
                        <input
                            type="text"
                            className="input-field"
                            value={c.trading_as}
                            onChange={(e) => setCompany('trading_as', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Registered address</label>
                        <textarea
                            rows={3}
                            className="input-field"
                            value={c.registered_address}
                            onChange={(e) => setCompany('registered_address', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Postal address</label>
                        <textarea
                            rows={3}
                            className="input-field"
                            value={c.postal_address}
                            onChange={(e) => setCompany('postal_address', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Invoice address</label>
                        <select
                            className="input-field"
                            value={c.invoice_address_type}
                            onChange={(e) => setCompany('invoice_address_type', e.target.value)}
                        >
                            <option value="registered">Registered address</option>
                            <option value="postal">Postal address</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    {c.invoice_address_type === 'custom' && (
                        <div className="sm:col-span-2">
                            <label className="label-field">Custom invoice address</label>
                            <textarea
                                rows={3}
                                className="input-field"
                                value={c.invoice_address_custom}
                                onChange={(e) => setCompany('invoice_address_custom', e.target.value)}
                            />
                        </div>
                    )}
                    <div>
                        <label className="label-field">Primary company email</label>
                        <input
                            type="email"
                            className="input-field"
                            value={c.primary_email}
                            onChange={(e) => setCompany('primary_email', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Email domain</label>
                        <input
                            type="text"
                            className="input-field"
                            value={c.email_domain}
                            onChange={(e) => setCompany('email_domain', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Telephone</label>
                        <input
                            type="text"
                            className="input-field"
                            value={c.telephone}
                            onChange={(e) => setCompany('telephone', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Turnover (£)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className="input-field"
                            value={c.turnover}
                            onChange={(e) => setCompany('turnover', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Date of trading</label>
                        <input
                            type="date"
                            className="input-field"
                            value={c.date_of_trading || ''}
                            onChange={(e) => setCompany('date_of_trading', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">SIC code</label>
                        <select
                            className="input-field"
                            value={c.sic_code_id}
                            onChange={(e) => setCompany('sic_code_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sicCodes.map((s) => (
                                <option key={s.id} value={s.id}>
                                    {s.code} — {s.description}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Nature of business</label>
                        <input
                            type="text"
                            className="input-field"
                            value={c.nature_of_business}
                            onChange={(e) => setCompany('nature_of_business', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Corporation tax office</label>
                        <input
                            type="text"
                            className="input-field"
                            value={c.corporation_tax_office}
                            onChange={(e) => setCompany('corporation_tax_office', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Company UTR</label>
                        <input
                            type="text"
                            className="input-field"
                            value={c.company_utr}
                            onChange={(e) => setCompany('company_utr', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Companies House authentication code</label>
                        <input
                            type="text"
                            className="input-field"
                            value={c.companies_house_auth_code}
                            onChange={(e) => setCompany('companies_house_auth_code', e.target.value)}
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Income details
                </summary>
                <div className="px-6 py-6">
                    <label htmlFor="income_details" className="label-field">
                        Income details
                    </label>
                    <textarea
                        id="income_details"
                        rows={4}
                        className="input-field"
                        value={form.data.income_details}
                        onChange={(e) => form.setData('income_details', e.target.value)}
                    />
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Previous accountant
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div className="sm:col-span-2">
                        <label htmlFor="previous_accountant_name" className="label-field">
                            Name
                        </label>
                        <input
                            id="previous_accountant_name"
                            type="text"
                            className="input-field"
                            value={form.data.previous_accountant_name}
                            onChange={(e) => form.setData('previous_accountant_name', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label htmlFor="previous_accountant_details" className="label-field">
                            Details
                        </label>
                        <textarea
                            id="previous_accountant_details"
                            rows={3}
                            className="input-field"
                            value={form.data.previous_accountant_details}
                            onChange={(e) => form.setData('previous_accountant_details', e.target.value)}
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Other details
                </summary>
                <div className="px-6 py-6">
                    <label htmlFor="other_details" className="label-field">
                        Other details
                    </label>
                    <textarea
                        id="other_details"
                        rows={4}
                        className="input-field"
                        value={form.data.other_details}
                        onChange={(e) => form.setData('other_details', e.target.value)}
                    />
                </div>
            </details>

            {extendedLookups && (
                <ClientExtendedSections form={form} lookups={extendedLookups} />
            )}

            <div className="flex flex-wrap items-center justify-between gap-4">
                <Link href="/clients" className="btn-secondary">
                    Cancel
                </Link>
                <div className="flex flex-wrap justify-end gap-3">
                    {mode === 'create' && (
                        <button
                            type="button"
                            className="btn-secondary"
                            disabled={form.processing}
                            onClick={(e) => submit(e, true)}
                        >
                            {form.processing ? 'Saving…' : 'Create and continue onboarding'}
                        </button>
                    )}
                    <button type="submit" className="btn-primary" disabled={form.processing}>
                        {form.processing ? 'Saving…' : mode === 'create' ? 'Create client' : 'Save changes'}
                    </button>
                </div>
            </div>
        </form>
    );
}
