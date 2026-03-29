function FieldError({ form, name }) {
    const msg = form.errors[name];
    if (!msg) {
        return null;
    }
    return <p className="mt-1 text-sm text-red-600">{msg}</p>;
}

export default function ClientExtendedSections({ form, lookups }) {
    const s = (section, key, value) => {
        form.setData(section, { ...form.data[section], [key]: value });
    };

    const setService = (index, key, value) => {
        const next = form.data.services.map((row, i) =>
            i === index ? { ...row, [key]: value } : row
        );
        form.setData('services', next);
    };

    const setMain = (key, value) => {
        form.setData('main_contact', { ...form.data.main_contact, [key]: value });
    };

    const cp = form.data.combined_pricing;
    const mc = form.data.main_contact;
    const ar = form.data.accounts_returns;
    const cs = form.data.confirmation_statement;
    const vat = form.data.vat;
    const paye = form.data.paye;
    const cis = form.data.cis;
    const ae = form.data.auto_enrolment;
    const p11d = form.data.p11d;
    const reg = form.data.registration;

    const sel = (items) =>
        (items ?? []).map((row) => (
            <option key={row.id} value={row.id}>
                {row.name}
            </option>
        ));

    return (
        <>
            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Combined pricing
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <label className="flex items-center gap-2 sm:col-span-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!cp.annual_charge_enabled}
                            onChange={(e) =>
                                s('combined_pricing', 'annual_charge_enabled', e.target.checked)
                            }
                        />
                        <span className="text-sm text-slate-700">Annual charge</span>
                    </label>
                    {cp.annual_charge_enabled && (
                        <div>
                            <label className="label-field">Annual amount (£)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                className="input-field"
                                value={cp.annual_charge ?? ''}
                                onChange={(e) => s('combined_pricing', 'annual_charge', e.target.value)}
                            />
                            <FieldError form={form} name="combined_pricing.annual_charge" />
                        </div>
                    )}
                    <label className="flex items-center gap-2 sm:col-span-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!cp.monthly_charge_enabled}
                            onChange={(e) =>
                                s('combined_pricing', 'monthly_charge_enabled', e.target.checked)
                            }
                        />
                        <span className="text-sm text-slate-700">Monthly charge</span>
                    </label>
                    {cp.monthly_charge_enabled && (
                        <div>
                            <label className="label-field">Monthly amount (£)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                className="input-field"
                                value={cp.monthly_charge ?? ''}
                                onChange={(e) => s('combined_pricing', 'monthly_charge', e.target.value)}
                            />
                            <FieldError form={form} name="combined_pricing.monthly_charge" />
                        </div>
                    )}
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Services &amp; fees
                </summary>
                <div className="overflow-x-auto px-6 py-6">
                    <table className="min-w-full text-sm">
                        <thead>
                            <tr className="border-b border-slate-200 text-left text-slate-500">
                                <th className="py-2 pr-4">Service</th>
                                <th className="py-2 pr-4">Enabled</th>
                                <th className="py-2">Fee (£)</th>
                            </tr>
                        </thead>
                        <tbody>
                            {(form.data.services ?? []).map((row, idx) => (
                                <tr key={row.service_id} className="border-b border-slate-100">
                                    <td className="py-2 pr-4 text-slate-900">{row.name}</td>
                                    <td className="py-2 pr-4">
                                        <input
                                            type="checkbox"
                                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                            checked={!!row.is_enabled}
                                            onChange={(e) =>
                                                setService(idx, 'is_enabled', e.target.checked)
                                            }
                                        />
                                    </td>
                                    <td className="py-2">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            className="input-field max-w-[8rem]"
                                            value={row.fee ?? ''}
                                            onChange={(e) =>
                                                setService(idx, 'fee', e.target.value || null)
                                            }
                                        />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    <FieldError form={form} name="services" />
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Main contact (global contact)
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <label className="label-field">Title</label>
                        <select
                            className="input-field"
                            value={mc.title_id ?? ''}
                            onChange={(e) => setMain('title_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.titles)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">First name</label>
                        <input
                            type="text"
                            className="input-field"
                            value={mc.first_name ?? ''}
                            onChange={(e) => setMain('first_name', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Middle name</label>
                        <input
                            type="text"
                            className="input-field"
                            value={mc.middle_name ?? ''}
                            onChange={(e) => setMain('middle_name', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Last name</label>
                        <input
                            type="text"
                            className="input-field"
                            value={mc.last_name ?? ''}
                            onChange={(e) => setMain('last_name', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Preferred name</label>
                        <input
                            type="text"
                            className="input-field"
                            value={mc.preferred_name ?? ''}
                            onChange={(e) => setMain('preferred_name', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Date of birth</label>
                        <input
                            type="date"
                            className="input-field"
                            value={mc.date_of_birth || ''}
                            onChange={(e) => setMain('date_of_birth', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Email</label>
                        <input
                            type="email"
                            className="input-field"
                            value={mc.email ?? ''}
                            onChange={(e) => setMain('email', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Portal login email</label>
                        <input
                            type="email"
                            className="input-field"
                            value={mc.portal_login_email ?? ''}
                            onChange={(e) => setMain('portal_login_email', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Postal address</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={mc.postal_address ?? ''}
                            onChange={(e) => setMain('postal_address', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Telephone</label>
                        <input
                            type="text"
                            className="input-field"
                            value={mc.telephone_number ?? ''}
                            onChange={(e) => setMain('telephone_number', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Mobile</label>
                        <input
                            type="text"
                            className="input-field"
                            value={mc.mobile_number ?? ''}
                            onChange={(e) => setMain('mobile_number', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">NI number</label>
                        <input
                            type="text"
                            className="input-field"
                            value={mc.ni_number ?? ''}
                            onChange={(e) => setMain('ni_number', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Personal UTR</label>
                        <input
                            type="text"
                            className="input-field"
                            value={mc.personal_utr ?? ''}
                            onChange={(e) => setMain('personal_utr', e.target.value)}
                        />
                    </div>
                    <label className="flex items-center gap-2 sm:col-span-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!mc.create_self_assessment}
                            onChange={(e) => setMain('create_self_assessment', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Create self assessment</span>
                    </label>
                    <div>
                        <label className="label-field">Self assessment fee (£)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className="input-field"
                            value={mc.self_assessment_fee ?? ''}
                            onChange={(e) => setMain('self_assessment_fee', e.target.value)}
                        />
                    </div>
                    <label className="flex items-center gap-2 sm:col-span-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!mc.client_does_own_sa}
                            onChange={(e) => setMain('client_does_own_sa', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Client does own SA</span>
                    </label>
                    <FieldError form={form} name="main_contact.email" />
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Accounts &amp; returns
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <label className="label-field">Accounts period end</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ar.accounts_period_end || ''}
                            onChange={(e) => s('accounts_returns', 'accounts_period_end', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">CH year end</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ar.ch_year_end || ''}
                            onChange={(e) => s('accounts_returns', 'ch_year_end', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">HMRC year end</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ar.hmrc_year_end || ''}
                            onChange={(e) => s('accounts_returns', 'hmrc_year_end', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">CH accounts next due</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ar.ch_accounts_next_due || ''}
                            onChange={(e) => s('accounts_returns', 'ch_accounts_next_due', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">CT600 due</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ar.ct600_due || ''}
                            onChange={(e) => s('accounts_returns', 'ct600_due', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Corporation tax due (£)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className="input-field"
                            value={ar.corporation_tax_amount_due ?? ''}
                            onChange={(e) => s('accounts_returns', 'corporation_tax_amount_due', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Tax due (HMRC year end)</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ar.tax_due_hmrc_year_end || ''}
                            onChange={(e) => s('accounts_returns', 'tax_due_hmrc_year_end', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">CT payment reference</label>
                        <input
                            type="text"
                            className="input-field"
                            value={ar.ct_payment_reference ?? ''}
                            onChange={(e) => s('accounts_returns', 'ct_payment_reference', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Tax office</label>
                        <select
                            className="input-field"
                            value={ar.tax_office_id ?? ''}
                            onChange={(e) => s('accounts_returns', 'tax_office_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.tax_offices)}
                        </select>
                    </div>
                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!ar.ch_email_reminder}
                            onChange={(e) => s('accounts_returns', 'ch_email_reminder', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">CH email reminder</span>
                    </label>
                    <div>
                        <label className="label-field">Latest action</label>
                        <select
                            className="input-field"
                            value={ar.latest_action_id ?? ''}
                            onChange={(e) => s('accounts_returns', 'latest_action_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.action_statuses)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">Latest action date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ar.latest_action_date || ''}
                            onChange={(e) => s('accounts_returns', 'latest_action_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Records received</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ar.records_received || ''}
                            onChange={(e) => s('accounts_returns', 'records_received', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Progress note</label>
                        <textarea
                            rows={3}
                            className="input-field"
                            value={ar.progress_note ?? ''}
                            onChange={(e) => s('accounts_returns', 'progress_note', e.target.value)}
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Confirmation statement
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <label className="label-field">Statement date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={cs.statement_date || ''}
                            onChange={(e) => s('confirmation_statement', 'statement_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Statement due</label>
                        <input
                            type="date"
                            className="input-field"
                            value={cs.statement_due || ''}
                            onChange={(e) => s('confirmation_statement', 'statement_due', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Latest action</label>
                        <select
                            className="input-field"
                            value={cs.latest_action_id ?? ''}
                            onChange={(e) => s('confirmation_statement', 'latest_action_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.action_statuses)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">Latest action date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={cs.latest_action_date || ''}
                            onChange={(e) => s('confirmation_statement', 'latest_action_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Records received</label>
                        <input
                            type="date"
                            className="input-field"
                            value={cs.records_received || ''}
                            onChange={(e) => s('confirmation_statement', 'records_received', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Progress note</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={cs.progress_note ?? ''}
                            onChange={(e) => s('confirmation_statement', 'progress_note', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Officers</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={cs.officers ?? ''}
                            onChange={(e) => s('confirmation_statement', 'officers', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Share capital</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={cs.share_capital ?? ''}
                            onChange={(e) => s('confirmation_statement', 'share_capital', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Shareholders</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={cs.shareholders ?? ''}
                            onChange={(e) => s('confirmation_statement', 'shareholders', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">People with significant control</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={cs.people_with_significant_control ?? ''}
                            onChange={(e) =>
                                s('confirmation_statement', 'people_with_significant_control', e.target.value)
                            }
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    VAT
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <label className="label-field">Frequency</label>
                        <select
                            className="input-field"
                            value={vat.vat_frequency_id ?? ''}
                            onChange={(e) => s('vat', 'vat_frequency_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.vat_frequencies)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">VAT period end</label>
                        <input
                            type="date"
                            className="input-field"
                            value={vat.vat_period_end || ''}
                            onChange={(e) => s('vat', 'vat_period_end', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Next return due</label>
                        <input
                            type="date"
                            className="input-field"
                            value={vat.next_return_due || ''}
                            onChange={(e) => s('vat', 'next_return_due', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">VAT bill (£)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className="input-field"
                            value={vat.vat_bill_amount ?? ''}
                            onChange={(e) => s('vat', 'vat_bill_amount', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">VAT bill due</label>
                        <input
                            type="date"
                            className="input-field"
                            value={vat.vat_bill_due || ''}
                            onChange={(e) => s('vat', 'vat_bill_due', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Latest action</label>
                        <select
                            className="input-field"
                            value={vat.latest_action_id ?? ''}
                            onChange={(e) => s('vat', 'latest_action_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.action_statuses)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">Latest action date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={vat.latest_action_date || ''}
                            onChange={(e) => s('vat', 'latest_action_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Records received</label>
                        <input
                            type="date"
                            className="input-field"
                            value={vat.records_received || ''}
                            onChange={(e) => s('vat', 'records_received', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Progress note</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={vat.progress_note ?? ''}
                            onChange={(e) => s('vat', 'progress_note', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Member state</label>
                        <select
                            className="input-field"
                            value={vat.vat_member_state_id ?? ''}
                            onChange={(e) => s('vat', 'vat_member_state_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.vat_member_states)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">VAT number</label>
                        <input
                            type="text"
                            className="input-field"
                            value={vat.vat_number ?? ''}
                            onChange={(e) => s('vat', 'vat_number', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">VAT address</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={vat.vat_address ?? ''}
                            onChange={(e) => s('vat', 'vat_address', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Date of registration</label>
                        <input
                            type="date"
                            className="input-field"
                            value={vat.date_of_registration || ''}
                            onChange={(e) => s('vat', 'date_of_registration', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Effective date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={vat.effective_date || ''}
                            onChange={(e) => s('vat', 'effective_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Estimated turnover</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className="input-field"
                            value={vat.estimated_turnover ?? ''}
                            onChange={(e) => s('vat', 'estimated_turnover', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Applied for MTD</label>
                        <input
                            type="date"
                            className="input-field"
                            value={vat.applied_for_mtd || ''}
                            onChange={(e) => s('vat', 'applied_for_mtd', e.target.value)}
                        />
                    </div>
                    {[
                        ['mtd_ready', 'MTD ready'],
                        ['transfer_of_going_concern', 'Transfer of going concern'],
                        ['involved_in_other_businesses', 'Involved in other businesses'],
                        ['direct_debit', 'Direct debit'],
                        ['standard_scheme', 'Standard scheme'],
                        ['cash_accounting_scheme', 'Cash accounting'],
                        ['retail_scheme', 'Retail scheme'],
                        ['margin_scheme', 'Margin scheme'],
                        ['flat_rate', 'Flat rate'],
                    ].map(([key, label]) => (
                        <label key={key} className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                checked={!!vat[key]}
                                onChange={(e) => s('vat', key, e.target.checked)}
                            />
                            <span className="text-sm text-slate-700">{label}</span>
                        </label>
                    ))}
                    {vat.flat_rate && (
                        <div className="sm:col-span-2">
                            <label className="label-field">Flat rate category</label>
                            <select
                                className="input-field"
                                value={vat.flat_rate_category_id ?? ''}
                                onChange={(e) => s('vat', 'flat_rate_category_id', e.target.value)}
                            >
                                <option value="">—</option>
                                {(lookups.flat_rate_categories ?? []).map((row) => (
                                    <option key={row.id} value={row.id}>
                                        {row.name}
                                        {row.rate != null ? ` (${row.rate}%)` : ''}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}
                    <div>
                        <label className="label-field">Month last quarter submitted (1–12)</label>
                        <input
                            type="number"
                            min="1"
                            max="12"
                            className="input-field"
                            value={vat.month_last_quarter_submitted ?? ''}
                            onChange={(e) => s('vat', 'month_last_quarter_submitted', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Box 5 last quarter</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            className="input-field"
                            value={vat.box5_last_quarter_submitted ?? ''}
                            onChange={(e) => s('vat', 'box5_last_quarter_submitted', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">General notes</label>
                        <textarea
                            rows={3}
                            className="input-field"
                            value={vat.general_notes ?? ''}
                            onChange={(e) => s('vat', 'general_notes', e.target.value)}
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    PAYE
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <label className="label-field">Employer&apos;s reference</label>
                        <input
                            type="text"
                            className="input-field"
                            value={paye.employers_reference ?? ''}
                            onChange={(e) => s('paye', 'employers_reference', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Accounts office reference</label>
                        <input
                            type="text"
                            className="input-field"
                            value={paye.accounts_office_reference ?? ''}
                            onChange={(e) => s('paye', 'accounts_office_reference', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Years required</label>
                        <input
                            type="text"
                            className="input-field"
                            value={paye.years_required ?? ''}
                            onChange={(e) => s('paye', 'years_required', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Frequency</label>
                        <select
                            className="input-field"
                            value={paye.paye_frequency_id ?? ''}
                            onChange={(e) => s('paye', 'paye_frequency_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.paye_frequencies)}
                        </select>
                    </div>
                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!paye.irregular_monthly_pay}
                            onChange={(e) => s('paye', 'irregular_monthly_pay', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Irregular monthly pay</span>
                    </label>
                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!paye.nil_eps}
                            onChange={(e) => s('paye', 'nil_eps', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Nil EPS</span>
                    </label>
                    <div>
                        <label className="label-field">No. of employees</label>
                        <input
                            type="number"
                            min="0"
                            className="input-field"
                            value={paye.no_of_employees ?? ''}
                            onChange={(e) => s('paye', 'no_of_employees', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Salary details</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={paye.salary_details ?? ''}
                            onChange={(e) => s('paye', 'salary_details', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">First pay date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={paye.first_pay_date || ''}
                            onChange={(e) => s('paye', 'first_pay_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">RTI deadline</label>
                        <input
                            type="date"
                            className="input-field"
                            value={paye.rti_deadline || ''}
                            onChange={(e) => s('paye', 'rti_deadline', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">PAYE scheme ceased</label>
                        <input
                            type="date"
                            className="input-field"
                            value={paye.paye_scheme_ceased || ''}
                            onChange={(e) => s('paye', 'paye_scheme_ceased', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Latest action</label>
                        <select
                            className="input-field"
                            value={paye.latest_action_id ?? ''}
                            onChange={(e) => s('paye', 'latest_action_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.action_statuses)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">Latest action date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={paye.latest_action_date || ''}
                            onChange={(e) => s('paye', 'latest_action_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Records received</label>
                        <input
                            type="date"
                            className="input-field"
                            value={paye.records_received || ''}
                            onChange={(e) => s('paye', 'records_received', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Progress note</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={paye.progress_note ?? ''}
                            onChange={(e) => s('paye', 'progress_note', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">General notes</label>
                        <textarea
                            rows={3}
                            className="input-field"
                            value={paye.general_notes ?? ''}
                            onChange={(e) => s('paye', 'general_notes', e.target.value)}
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    CIS
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!cis.is_contractor}
                            onChange={(e) => s('cis', 'is_contractor', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Contractor</span>
                    </label>
                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!cis.is_subcontractor}
                            onChange={(e) => s('cis', 'is_subcontractor', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Subcontractor</span>
                    </label>
                    <div>
                        <label className="label-field">CIS date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={cis.cis_date || ''}
                            onChange={(e) => s('cis', 'cis_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">CIS deadline</label>
                        <input
                            type="date"
                            className="input-field"
                            value={cis.cis_deadline || ''}
                            onChange={(e) => s('cis', 'cis_deadline', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Latest action</label>
                        <select
                            className="input-field"
                            value={cis.latest_action_id ?? ''}
                            onChange={(e) => s('cis', 'latest_action_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.action_statuses)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">Latest action date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={cis.latest_action_date || ''}
                            onChange={(e) => s('cis', 'latest_action_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Records received</label>
                        <input
                            type="date"
                            className="input-field"
                            value={cis.records_received || ''}
                            onChange={(e) => s('cis', 'records_received', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Progress note</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={cis.progress_note ?? ''}
                            onChange={(e) => s('cis', 'progress_note', e.target.value)}
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Auto-enrolment
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <label className="label-field">Latest action</label>
                        <select
                            className="input-field"
                            value={ae.latest_action_id ?? ''}
                            onChange={(e) => s('auto_enrolment', 'latest_action_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.action_statuses)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">Latest action date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ae.latest_action_date || ''}
                            onChange={(e) => s('auto_enrolment', 'latest_action_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Records received</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ae.records_received || ''}
                            onChange={(e) => s('auto_enrolment', 'records_received', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Progress note</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={ae.progress_note ?? ''}
                            onChange={(e) => s('auto_enrolment', 'progress_note', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Staging date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ae.staging_date || ''}
                            onChange={(e) => s('auto_enrolment', 'staging_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Postponement date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ae.postponement_date || ''}
                            onChange={(e) => s('auto_enrolment', 'postponement_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Pensions regulator opt-out</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ae.pensions_regulator_opt_out_date || ''}
                            onChange={(e) =>
                                s('auto_enrolment', 'pensions_regulator_opt_out_date', e.target.value)
                            }
                        />
                    </div>
                    <div>
                        <label className="label-field">Re-enrolment date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ae.re_enrolment_date || ''}
                            onChange={(e) => s('auto_enrolment', 're_enrolment_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Pension provider</label>
                        <input
                            type="text"
                            className="input-field"
                            value={ae.pension_provider ?? ''}
                            onChange={(e) => s('auto_enrolment', 'pension_provider', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Pension ID</label>
                        <input
                            type="text"
                            className="input-field"
                            value={ae.pension_id ?? ''}
                            onChange={(e) => s('auto_enrolment', 'pension_id', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Declaration of compliance due</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ae.declaration_of_compliance_due || ''}
                            onChange={(e) => s('auto_enrolment', 'declaration_of_compliance_due', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Declaration of compliance submitted</label>
                        <input
                            type="date"
                            className="input-field"
                            value={ae.declaration_of_compliance_submission || ''}
                            onChange={(e) =>
                                s('auto_enrolment', 'declaration_of_compliance_submission', e.target.value)
                            }
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    P11D
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <div>
                        <label className="label-field">Next return due</label>
                        <input
                            type="date"
                            className="input-field"
                            value={p11d.next_return_due || ''}
                            onChange={(e) => s('p11d', 'next_return_due', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Latest submitted</label>
                        <input
                            type="date"
                            className="input-field"
                            value={p11d.latest_submitted || ''}
                            onChange={(e) => s('p11d', 'latest_submitted', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Latest action</label>
                        <select
                            className="input-field"
                            value={p11d.latest_action_id ?? ''}
                            onChange={(e) => s('p11d', 'latest_action_id', e.target.value)}
                        >
                            <option value="">—</option>
                            {sel(lookups.action_statuses)}
                        </select>
                    </div>
                    <div>
                        <label className="label-field">Latest action date</label>
                        <input
                            type="date"
                            className="input-field"
                            value={p11d.latest_action_date || ''}
                            onChange={(e) => s('p11d', 'latest_action_date', e.target.value)}
                        />
                    </div>
                    <div>
                        <label className="label-field">Records received</label>
                        <input
                            type="date"
                            className="input-field"
                            value={p11d.records_received || ''}
                            onChange={(e) => s('p11d', 'records_received', e.target.value)}
                        />
                    </div>
                    <div className="sm:col-span-2">
                        <label className="label-field">Progress note</label>
                        <textarea
                            rows={2}
                            className="input-field"
                            value={p11d.progress_note ?? ''}
                            onChange={(e) => s('p11d', 'progress_note', e.target.value)}
                        />
                    </div>
                </div>
            </details>

            <details className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <summary className="cursor-pointer list-none border-b border-slate-100 px-6 py-4 text-base font-semibold text-slate-900">
                    Registration &amp; onboarding
                </summary>
                <div className="grid gap-4 px-6 py-6 sm:grid-cols-2">
                    <label className="flex items-center gap-2 sm:col-span-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!reg.terms_signed_fee_paid}
                            onChange={(e) => s('registration', 'terms_signed_fee_paid', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Terms signed &amp; fee paid</span>
                    </label>
                    {reg.terms_signed_fee_paid && (
                        <div>
                            <label className="label-field">Registration fee (£)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                className="input-field"
                                value={reg.registration_fee ?? ''}
                                onChange={(e) => s('registration', 'registration_fee', e.target.value)}
                            />
                        </div>
                    )}
                    <div>
                        <label className="label-field">Letter of engagement signed</label>
                        <input
                            type="date"
                            className="input-field"
                            value={reg.letter_of_engagement_signed || ''}
                            onChange={(e) => s('registration', 'letter_of_engagement_signed', e.target.value)}
                        />
                    </div>
                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                            checked={!!reg.money_laundering_complete}
                            onChange={(e) => s('registration', 'money_laundering_complete', e.target.checked)}
                        />
                        <span className="text-sm text-slate-700">Money laundering complete</span>
                    </label>
                    <div>
                        <label className="label-field">64-8 registration</label>
                        <input
                            type="date"
                            className="input-field"
                            value={reg.sixty_four_eight_registration || ''}
                            onChange={(e) => s('registration', 'sixty_four_eight_registration', e.target.value)}
                        />
                    </div>
                </div>
            </details>
        </>
    );
}
