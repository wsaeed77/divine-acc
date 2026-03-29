import { Head, Link, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

export default function Index({ clients, filters, clientTypes, partnerOptions, managerOptions }) {
    const { flash } = usePage().props;

    const applyFilters = (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const params = {};
        fd.forEach((v, k) => {
            if (v !== '') {
                params[k] = v;
            }
        });
        router.get('/clients', params, { preserveState: true, replace: true });
    };

    return (
        <AuthenticatedLayout header="Clients">
            <Head title="Clients" />

            {flash?.success && (
                <div className="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-900 ring-1 ring-emerald-100">
                    {flash.success}
                </div>
            )}

            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <p className="text-sm text-slate-600">
                    Search and filter your client list. Compliance sections (VAT, PAYE, etc.) can be
                    layered on in later phases.
                </p>
                <Link href="/clients/create" className="btn-primary">
                    Add client
                </Link>
            </div>

            <form
                onSubmit={applyFilters}
                className="mb-6 flex flex-wrap items-end gap-4 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-slate-200/60"
            >
                <div className="min-w-[12rem] flex-1">
                    <label htmlFor="search" className="label-field">
                        Search
                    </label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        className="input-field"
                        placeholder="Name, ref, company no., UTR…"
                        defaultValue={filters.search ?? ''}
                    />
                </div>
                <div className="min-w-[10rem]">
                    <label htmlFor="client_type_id" className="label-field">
                        Type
                    </label>
                    <select
                        id="client_type_id"
                        name="client_type_id"
                        className="input-field"
                        defaultValue={filters.client_type_id ?? ''}
                    >
                        <option value="">All types</option>
                        {clientTypes.map((t) => (
                            <option key={t.id} value={t.id}>
                                {t.name}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="min-w-[10rem]">
                    <label htmlFor="partner_id" className="label-field">
                        Partner
                    </label>
                    <select
                        id="partner_id"
                        name="partner_id"
                        className="input-field"
                        defaultValue={filters.partner_id ?? ''}
                    >
                        <option value="">Any</option>
                        {partnerOptions.map((p) => (
                            <option key={p.id} value={p.id}>
                                {p.name}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="min-w-[10rem]">
                    <label htmlFor="manager_id" className="label-field">
                        Manager
                    </label>
                    <select
                        id="manager_id"
                        name="manager_id"
                        className="input-field"
                        defaultValue={filters.manager_id ?? ''}
                    >
                        <option value="">Any</option>
                        {managerOptions.map((m) => (
                            <option key={m.id} value={m.id}>
                                {m.name}
                            </option>
                        ))}
                    </select>
                </div>
                <div className="min-w-[8rem]">
                    <label htmlFor="status" className="label-field">
                        Status
                    </label>
                    <select
                        id="status"
                        name="status"
                        className="input-field"
                        defaultValue={filters.status ?? 'active'}
                    >
                        <option value="all">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" className="btn-secondary">
                    Apply
                </button>
            </form>

            <div className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead className="bg-slate-50/80">
                            <tr>
                                <th className="px-4 py-3 font-medium text-slate-600">Name</th>
                                <th className="px-4 py-3 font-medium text-slate-600">Reference</th>
                                <th className="px-4 py-3 font-medium text-slate-600">Type</th>
                                <th className="px-4 py-3 font-medium text-slate-600">Partner</th>
                                <th className="px-4 py-3 font-medium text-slate-600">Manager</th>
                                <th className="px-4 py-3 font-medium text-slate-600">Status</th>
                                <th className="px-4 py-3 font-medium text-slate-600">
                                    <span className="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {clients.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-10 text-center text-slate-500">
                                        No clients match your filters.{' '}
                                        <Link href="/clients/create" className="font-medium text-brand-700">
                                            Add a client
                                        </Link>
                                    </td>
                                </tr>
                            ) : (
                                clients.data.map((row) => (
                                    <tr key={row.id} className={!row.is_active ? 'bg-slate-50 opacity-80' : ''}>
                                        <td className="whitespace-nowrap px-4 py-3 font-medium text-slate-900">
                                            <Link
                                                href={`/clients/${row.id}`}
                                                className="text-brand-700 hover:text-brand-800"
                                            >
                                                {row.name}
                                            </Link>
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 font-mono text-xs text-slate-600">
                                            {row.internal_reference}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-slate-600">
                                            {row.client_type?.name}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-slate-600">
                                            {row.partner?.name ?? '—'}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-slate-600">
                                            {row.manager?.name ?? '—'}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3">
                                            {row.is_active ? (
                                                <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-800 ring-1 ring-emerald-100">
                                                    Active
                                                </span>
                                            ) : (
                                                <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                                                    Inactive
                                                </span>
                                            )}
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-right">
                                            <Link
                                                href={`/clients/${row.id}/edit`}
                                                className="text-sm font-medium text-brand-700 hover:text-brand-800"
                                            >
                                                Edit
                                            </Link>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>
                {clients.links && clients.links.length > 3 && (
                    <div className="flex flex-wrap items-center justify-center gap-1 border-t border-slate-100 px-4 py-3">
                        {clients.links.map((link, i) => (
                            <Link
                                key={i}
                                href={link.url || '#'}
                                className={`rounded px-3 py-1 text-sm ${
                                    link.active
                                        ? 'bg-brand-700 text-white'
                                        : 'text-slate-600 hover:bg-slate-100'
                                } ${!link.url ? 'pointer-events-none opacity-40' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
