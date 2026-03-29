import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';
import {
    BuildingOffice2Icon,
    UserGroupIcon,
    UserPlusIcon,
} from '@heroicons/react/24/outline';

function roleLabel(role, roleOptions) {
    return roleOptions.find((o) => o.value === role)?.label ?? role;
}

export default function Index({ firm, team, canManage, roleOptions }) {
    const { flash } = usePage().props;

    const firmForm = useForm({
        name: firm.name,
        email: firm.email ?? '',
        phone: firm.phone ?? '',
        address: firm.address ?? '',
        primary_color: firm.primary_color,
        logo: null,
    });

    useEffect(() => {
        firmForm.setData({
            name: firm.name,
            email: firm.email ?? '',
            phone: firm.phone ?? '',
            address: firm.address ?? '',
            primary_color: firm.primary_color,
            logo: null,
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps -- sync when server props refresh after save
    }, [
        firm.name,
        firm.email,
        firm.phone,
        firm.address,
        firm.primary_color,
        firm.logo_url,
    ]);

    const teamForm = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: 'staff',
    });

    const submitFirm = (e) => {
        e.preventDefault();
        firmForm.post('/settings/firm', {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const submitTeam = (e) => {
        e.preventDefault();
        teamForm.post('/settings/team', {
            preserveScroll: true,
            onSuccess: () => teamForm.reset(),
        });
    };

    const removeMember = (id) => {
        if (!confirm('Remove this person from your firm? They will no longer be able to sign in.')) {
            return;
        }
        router.delete(`/settings/team/${id}`, { preserveScroll: true });
    };

    const primary = firm.primary_color || '#0f766e';

    return (
        <AuthenticatedLayout header="Settings">
            <Head title="Settings" />

            {(flash?.success || flash?.error) && (
                <div
                    className={`mb-6 rounded-xl px-4 py-3 text-sm ring-1 ${
                        flash?.error
                            ? 'bg-red-50 text-red-800 ring-red-100'
                            : 'bg-emerald-50 text-emerald-900 ring-emerald-100'
                    }`}
                >
                    {flash?.error ?? flash?.success}
                </div>
            )}

            <div className="grid gap-8 lg:grid-cols-2">
                <section className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                    <div className="border-b border-slate-100 px-6 py-4">
                        <div className="flex items-center gap-2 text-slate-900">
                            <BuildingOffice2Icon
                                className="h-5 w-5"
                                style={{ color: primary }}
                            />
                            <h2 className="text-base font-semibold">Firm profile</h2>
                        </div>
                        <p className="mt-1 text-sm text-slate-600">
                            How your practice appears in the workspace.
                        </p>
                    </div>

                    {canManage ? (
                        <form onSubmit={submitFirm} className="space-y-5 px-6 py-6">
                            <div>
                                <label htmlFor="firm_name" className="label-field">
                                    Firm name
                                </label>
                                <input
                                    id="firm_name"
                                    type="text"
                                    className="input-field"
                                    value={firmForm.data.name}
                                    onChange={(e) => firmForm.setData('name', e.target.value)}
                                    required
                                />
                                {firmForm.errors.name && (
                                    <p className="mt-1 text-sm text-red-600">{firmForm.errors.name}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="firm_slug" className="label-field">
                                    Workspace slug
                                </label>
                                <input
                                    id="firm_slug"
                                    type="text"
                                    className="input-field bg-slate-50"
                                    value={firm.slug}
                                    readOnly
                                    disabled
                                />
                                <p className="mt-1 text-xs text-slate-500">
                                    Set when your firm was registered; used internally.
                                </p>
                            </div>

                            <div>
                                <label htmlFor="firm_email" className="label-field">
                                    Firm email
                                </label>
                                <input
                                    id="firm_email"
                                    type="email"
                                    className="input-field"
                                    value={firmForm.data.email}
                                    onChange={(e) => firmForm.setData('email', e.target.value)}
                                />
                                {firmForm.errors.email && (
                                    <p className="mt-1 text-sm text-red-600">{firmForm.errors.email}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="firm_phone" className="label-field">
                                    Phone
                                </label>
                                <input
                                    id="firm_phone"
                                    type="text"
                                    className="input-field"
                                    value={firmForm.data.phone}
                                    onChange={(e) => firmForm.setData('phone', e.target.value)}
                                />
                                {firmForm.errors.phone && (
                                    <p className="mt-1 text-sm text-red-600">{firmForm.errors.phone}</p>
                                )}
                            </div>

                            <div>
                                <label htmlFor="firm_address" className="label-field">
                                    Address
                                </label>
                                <textarea
                                    id="firm_address"
                                    rows={3}
                                    className="input-field"
                                    value={firmForm.data.address}
                                    onChange={(e) => firmForm.setData('address', e.target.value)}
                                />
                                {firmForm.errors.address && (
                                    <p className="mt-1 text-sm text-red-600">{firmForm.errors.address}</p>
                                )}
                            </div>

                            <div className="grid gap-4 sm:grid-cols-[auto_1fr] sm:items-end">
                                <div>
                                    <label htmlFor="firm_color" className="label-field">
                                        Brand colour
                                    </label>
                                    <input
                                        id="firm_color_pick"
                                        type="color"
                                        className="h-10 w-14 cursor-pointer rounded border border-slate-300 bg-white p-0.5"
                                        value={firmForm.data.primary_color}
                                        onChange={(e) =>
                                            firmForm.setData('primary_color', e.target.value)
                                        }
                                    />
                                </div>
                                <div>
                                    <label htmlFor="firm_color_hex" className="label-field sm:sr-only">
                                        Hex
                                    </label>
                                    <input
                                        id="firm_color_hex"
                                        type="text"
                                        className="input-field font-mono text-sm"
                                        value={firmForm.data.primary_color}
                                        onChange={(e) =>
                                            firmForm.setData('primary_color', e.target.value)
                                        }
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        placeholder="#0f766e"
                                    />
                                    {firmForm.errors.primary_color && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {firmForm.errors.primary_color}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label htmlFor="firm_logo" className="label-field">
                                    Logo
                                </label>
                                {firm.logo_url && (
                                    <img
                                        src={firm.logo_url}
                                        alt=""
                                        className="mb-2 h-12 w-12 rounded-lg object-cover ring-1 ring-slate-200"
                                    />
                                )}
                                <input
                                    id="firm_logo"
                                    type="file"
                                    accept="image/*"
                                    className="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-800 hover:file:bg-brand-100"
                                    onChange={(e) =>
                                        firmForm.setData('logo', e.target.files?.[0] ?? null)
                                    }
                                />
                                {firmForm.errors.logo && (
                                    <p className="mt-1 text-sm text-red-600">{firmForm.errors.logo}</p>
                                )}
                                <p className="mt-1 text-xs text-slate-500">
                                    Optional. PNG or JPG, max 2&nbsp;MB. Run{' '}
                                    <code className="rounded bg-slate-100 px-1">php artisan storage:link</code>{' '}
                                    if the logo does not display.
                                </p>
                            </div>

                            <div className="flex justify-end pt-2">
                                <button
                                    type="submit"
                                    className="btn-primary"
                                    disabled={firmForm.processing}
                                >
                                    {firmForm.processing ? 'Saving…' : 'Save firm profile'}
                                </button>
                            </div>
                        </form>
                    ) : (
                        <dl className="space-y-4 px-6 py-6 text-sm">
                            <div>
                                <dt className="font-medium text-slate-500">Firm name</dt>
                                <dd className="mt-0.5 text-slate-900">{firm.name}</dd>
                            </div>
                            <div>
                                <dt className="font-medium text-slate-500">Workspace slug</dt>
                                <dd className="mt-0.5 font-mono text-slate-900">{firm.slug}</dd>
                            </div>
                            {firm.email && (
                                <div>
                                    <dt className="font-medium text-slate-500">Firm email</dt>
                                    <dd className="mt-0.5 text-slate-900">{firm.email}</dd>
                                </div>
                            )}
                            {firm.phone && (
                                <div>
                                    <dt className="font-medium text-slate-500">Phone</dt>
                                    <dd className="mt-0.5 text-slate-900">{firm.phone}</dd>
                                </div>
                            )}
                            {firm.address && (
                                <div>
                                    <dt className="font-medium text-slate-500">Address</dt>
                                    <dd className="mt-0.5 whitespace-pre-wrap text-slate-900">
                                        {firm.address}
                                    </dd>
                                </div>
                            )}
                            <p className="rounded-lg bg-slate-50 px-3 py-2 text-slate-600 ring-1 ring-slate-100">
                                Only tenant admins can edit firm details. Ask an admin if something
                                needs updating.
                            </p>
                        </dl>
                    )}
                </section>

                <section className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                    <div className="border-b border-slate-100 px-6 py-4">
                        <div className="flex items-center gap-2 text-slate-900">
                            <UserGroupIcon
                                className="h-5 w-5"
                                style={{ color: primary }}
                            />
                            <h2 className="text-base font-semibold">Team</h2>
                        </div>
                        <p className="mt-1 text-sm text-slate-600">
                            People who can access this workspace.
                        </p>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead className="bg-slate-50/80">
                                <tr>
                                    <th className="px-6 py-3 font-medium text-slate-600">Name</th>
                                    <th className="px-6 py-3 font-medium text-slate-600">Email</th>
                                    <th className="px-6 py-3 font-medium text-slate-600">Role</th>
                                    {canManage && (
                                        <th className="px-6 py-3 font-medium text-slate-600">
                                            <span className="sr-only">Actions</span>
                                        </th>
                                    )}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {team.map((member) => (
                                    <tr key={member.id}>
                                        <td className="whitespace-nowrap px-6 py-3 font-medium text-slate-900">
                                            {member.name}
                                            {member.is_current && (
                                                <span className="ml-2 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-800 ring-1 ring-brand-100">
                                                    You
                                                </span>
                                            )}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-3 text-slate-600">
                                            {member.email}
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-3 text-slate-600">
                                            {roleLabel(member.role, roleOptions)}
                                        </td>
                                        {canManage && (
                                            <td className="whitespace-nowrap px-6 py-3 text-right">
                                                {!member.is_current && (
                                                    <button
                                                        type="button"
                                                        onClick={() => removeMember(member.id)}
                                                        className="text-sm font-medium text-red-600 hover:text-red-700"
                                                    >
                                                        Remove
                                                    </button>
                                                )}
                                            </td>
                                        )}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {canManage && (
                        <div className="border-t border-slate-100 px-6 py-6">
                            <div className="mb-4 flex items-center gap-2 text-slate-900">
                                <UserPlusIcon className="h-5 w-5 text-brand-700" />
                                <h3 className="text-sm font-semibold">Add team member</h3>
                            </div>
                            <form onSubmit={submitTeam} className="grid gap-4 sm:grid-cols-2">
                                <div className="sm:col-span-2">
                                    <label htmlFor="tm_name" className="label-field">
                                        Name
                                    </label>
                                    <input
                                        id="tm_name"
                                        type="text"
                                        className="input-field"
                                        value={teamForm.data.name}
                                        onChange={(e) => teamForm.setData('name', e.target.value)}
                                        required
                                        autoComplete="name"
                                    />
                                    {teamForm.errors.name && (
                                        <p className="mt-1 text-sm text-red-600">{teamForm.errors.name}</p>
                                    )}
                                </div>
                                <div className="sm:col-span-2">
                                    <label htmlFor="tm_email" className="label-field">
                                        Email
                                    </label>
                                    <input
                                        id="tm_email"
                                        type="email"
                                        className="input-field"
                                        value={teamForm.data.email}
                                        onChange={(e) => teamForm.setData('email', e.target.value)}
                                        required
                                        autoComplete="off"
                                    />
                                    {teamForm.errors.email && (
                                        <p className="mt-1 text-sm text-red-600">{teamForm.errors.email}</p>
                                    )}
                                </div>
                                <div>
                                    <label htmlFor="tm_password" className="label-field">
                                        Password
                                    </label>
                                    <input
                                        id="tm_password"
                                        type="password"
                                        className="input-field"
                                        value={teamForm.data.password}
                                        onChange={(e) => teamForm.setData('password', e.target.value)}
                                        required
                                        autoComplete="new-password"
                                    />
                                    {teamForm.errors.password && (
                                        <p className="mt-1 text-sm text-red-600">
                                            {teamForm.errors.password}
                                        </p>
                                    )}
                                </div>
                                <div>
                                    <label htmlFor="tm_password_confirmation" className="label-field">
                                        Confirm password
                                    </label>
                                    <input
                                        id="tm_password_confirmation"
                                        type="password"
                                        className="input-field"
                                        value={teamForm.data.password_confirmation}
                                        onChange={(e) =>
                                            teamForm.setData('password_confirmation', e.target.value)
                                        }
                                        required
                                        autoComplete="new-password"
                                    />
                                </div>
                                <div className="sm:col-span-2">
                                    <label htmlFor="tm_role" className="label-field">
                                        Role
                                    </label>
                                    <select
                                        id="tm_role"
                                        className="input-field"
                                        value={teamForm.data.role}
                                        onChange={(e) => teamForm.setData('role', e.target.value)}
                                    >
                                        {roleOptions.map((opt) => (
                                            <option key={opt.value} value={opt.value}>
                                                {opt.label}
                                            </option>
                                        ))}
                                    </select>
                                    {teamForm.errors.role && (
                                        <p className="mt-1 text-sm text-red-600">{teamForm.errors.role}</p>
                                    )}
                                </div>
                                <div className="sm:col-span-2 flex justify-end">
                                    <button
                                        type="submit"
                                        className="btn-secondary"
                                        disabled={teamForm.processing}
                                    >
                                        {teamForm.processing ? 'Adding…' : 'Add member'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}
                </section>
            </div>

            <p className="mt-8 text-center text-sm text-slate-500">
                <Link href="/dashboard" className="font-medium text-brand-700 hover:text-brand-800">
                    ← Back to dashboard
                </Link>
            </p>
        </AuthenticatedLayout>
    );
}
