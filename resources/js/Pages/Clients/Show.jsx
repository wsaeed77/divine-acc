import { Head, Link, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

function fmtDate(v) {
    if (!v) {
        return '—';
    }
    const d = new Date(v);
    if (Number.isNaN(d.getTime())) {
        return v;
    }
    return d.toLocaleDateString('en-GB');
}

function enabledServices(extended) {
    const rows = extended?.services ?? [];
    return rows.filter((s) => s.is_enabled).map((s) => s.name);
}

export default function Show({ client, extended, canDelete }) {
    const { flash } = usePage().props;
    const cd = client.company_detail;

    const deactivate = () => {
        if (
            !confirm(
                'Deactivate this client? They will be hidden from default lists but history is kept.'
            )
        ) {
            return;
        }
        router.delete(`/clients/${client.id}`);
    };

    return (
        <AuthenticatedLayout header={client.name}>
            <Head title={client.name} />

            {flash?.success && (
                <div className="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-900 ring-1 ring-emerald-100">
                    {flash.success}
                </div>
            )}

            <div className="mb-6 flex flex-wrap items-center gap-3">
                <Link href={`/clients/${client.id}/edit`} className="btn-primary">
                    Edit client
                </Link>
                <Link href="/clients" className="btn-secondary">
                    Back to list
                </Link>
                {canDelete && client.is_active && (
                    <button type="button" onClick={deactivate} className="btn-secondary text-red-700 ring-red-200 hover:bg-red-50">
                        Deactivate
                    </button>
                )}
            </div>

            <div className="grid gap-6 lg:grid-cols-2">
                <section className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60">
                    <h2 className="text-sm font-semibold text-slate-900">Overview</h2>
                    <dl className="mt-4 space-y-3 text-sm">
                        <div>
                            <dt className="text-slate-500">Internal reference</dt>
                            <dd className="font-mono text-slate-900">{client.internal_reference}</dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">Client type</dt>
                            <dd className="text-slate-900">{client.client_type?.name}</dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">Partner</dt>
                            <dd className="text-slate-900">{client.partner?.name ?? '—'}</dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">Manager</dt>
                            <dd className="text-slate-900">{client.manager?.name ?? '—'}</dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">Credit check</dt>
                            <dd className="text-slate-900">
                                {client.credit_check_completed
                                    ? `Yes · ${fmtDate(client.credit_check_date)}`
                                    : 'No'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">Status</dt>
                            <dd className="text-slate-900">
                                {client.is_active ? 'Active' : 'Inactive'}
                            </dd>
                        </div>
                    </dl>
                </section>

                <section className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60">
                    <h2 className="text-sm font-semibold text-slate-900">Company</h2>
                    {!cd ? (
                        <p className="mt-4 text-sm text-slate-600">No company details saved yet.</p>
                    ) : (
                        <dl className="mt-4 space-y-3 text-sm">
                            <div>
                                <dt className="text-slate-500">Company number</dt>
                                <dd className="text-slate-900">{cd.company_number ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Status</dt>
                                <dd className="text-slate-900">{cd.company_status?.name ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Trading as</dt>
                                <dd className="text-slate-900">{cd.trading_as ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">UTR</dt>
                                <dd className="font-mono text-slate-900">{cd.company_utr ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Primary email</dt>
                                <dd className="text-slate-900">{cd.primary_email ?? '—'}</dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Telephone</dt>
                                <dd className="text-slate-900">{cd.telephone ?? '—'}</dd>
                            </div>
                        </dl>
                    )}
                </section>

                {extended && (
                    <>
                        <section className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60 lg:col-span-2">
                            <h2 className="text-sm font-semibold text-slate-900">Services &amp; pricing</h2>
                            <dl className="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                                <div>
                                    <dt className="text-slate-500">Combined annual</dt>
                                    <dd className="text-slate-900">
                                        {extended.combined_pricing?.annual_charge_enabled
                                            ? `£${extended.combined_pricing.annual_charge ?? '—'}`
                                            : '—'}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-slate-500">Combined monthly</dt>
                                    <dd className="text-slate-900">
                                        {extended.combined_pricing?.monthly_charge_enabled
                                            ? `£${extended.combined_pricing.monthly_charge ?? '—'}`
                                            : '—'}
                                    </dd>
                                </div>
                                <div className="sm:col-span-2">
                                    <dt className="text-slate-500">Enabled services</dt>
                                    <dd className="text-slate-900">
                                        {(() => {
                                            const names = enabledServices(extended);
                                            return names.length ? names.join(', ') : '—';
                                        })()}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60">
                            <h2 className="text-sm font-semibold text-slate-900">Main contact</h2>
                            <dl className="mt-4 space-y-3 text-sm">
                                <div>
                                    <dt className="text-slate-500">Name</dt>
                                    <dd className="text-slate-900">
                                        {[extended.main_contact?.first_name, extended.main_contact?.last_name]
                                            .filter(Boolean)
                                            .join(' ') || '—'}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-slate-500">Email</dt>
                                    <dd className="text-slate-900">{extended.main_contact?.email ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-slate-500">Telephone</dt>
                                    <dd className="text-slate-900">
                                        {extended.main_contact?.telephone_number ?? '—'}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60">
                            <h2 className="text-sm font-semibold text-slate-900">Compliance snapshot</h2>
                            <dl className="mt-4 space-y-3 text-sm">
                                <div>
                                    <dt className="text-slate-500">VAT number</dt>
                                    <dd className="font-mono text-slate-900">{extended.vat?.vat_number ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-slate-500">PAYE ref</dt>
                                    <dd className="font-mono text-slate-900">
                                        {extended.paye?.employers_reference ?? '—'}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-slate-500">CIS</dt>
                                    <dd className="text-slate-900">
                                        {(() => {
                                            const bits = [];
                                            if (extended.cis?.is_contractor) {
                                                bits.push('Contractor');
                                            }
                                            if (extended.cis?.is_subcontractor) {
                                                bits.push('Subcontractor');
                                            }
                                            return bits.length ? bits.join(' · ') : '—';
                                        })()}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-slate-500">P11D next due</dt>
                                    <dd className="text-slate-900">{fmtDate(extended.p11d?.next_return_due)}</dd>
                                </div>
                                <div>
                                    <dt className="text-slate-500">Confirmation statement due</dt>
                                    <dd className="text-slate-900">
                                        {fmtDate(extended.confirmation_statement?.statement_due)}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="text-slate-500">Accounts (CH next due)</dt>
                                    <dd className="text-slate-900">
                                        {fmtDate(extended.accounts_returns?.ch_accounts_next_due)}
                                    </dd>
                                </div>
                            </dl>
                        </section>
                    </>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
