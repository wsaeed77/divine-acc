import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout';
import { BuildingOffice2Icon, SparklesIcon } from '@heroicons/react/24/outline';

export default function Dashboard() {
    return (
        <AuthenticatedLayout header="Dashboard">
            <Head title="Dashboard" />
            <div className="grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-2">
                    <div className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                        <div className="border-b border-slate-100 bg-gradient-to-r from-brand-50 to-white px-6 py-5">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700 text-white shadow-md">
                                    <SparklesIcon className="h-6 w-6" />
                                </div>
                                <div>
                                    <h2 className="text-base font-semibold text-slate-900">
                                        Welcome to Phase 1
                                    </h2>
                                    <p className="text-sm text-slate-600">
                                        Authentication and tenant workspaces are live. Clients and tasks
                                        come next.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div className="px-6 py-8">
                            <p className="text-sm leading-relaxed text-slate-600">
                                Your firm has its own isolated workspace. Next phases will add client
                                management, task automation, invoicing, and reporting — aligned with
                                your requirements documents in{' '}
                                <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-800">
                                    docs/
                                </code>
                                .
                            </p>
                            <ul className="mt-6 space-y-3 text-sm text-slate-600">
                                <li className="flex gap-2">
                                    <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" />
                                    Multi-tenant data (each accounting firm is a tenant)
                                </li>
                                <li className="flex gap-2">
                                    <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" />
                                    First user is <strong className="font-medium text-slate-800">tenant admin</strong>
                                </li>
                                <li className="flex gap-2">
                                    <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" />
                                    Polished Inertia + React + Tailwind UI
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60">
                    <div className="flex items-center gap-2 text-slate-900">
                        <BuildingOffice2Icon className="h-5 w-5 text-brand-700" />
                        <h3 className="text-sm font-semibold">Quick links</h3>
                    </div>
                    <ul className="mt-4 space-y-2 text-sm text-slate-600">
                        <li>
                            <Link
                                href="/clients"
                                className="font-medium text-brand-700 hover:text-brand-800"
                            >
                                Clients
                            </Link>{' '}
                            — firm client list & company details
                        </li>
                        <li>
                            <span className="text-slate-400">Tasks</span> — coming in Phase 2
                        </li>
                        <li>
                            <Link
                                href="/settings"
                                className="font-medium text-brand-700 hover:text-brand-800"
                            >
                                Settings
                            </Link>{' '}
                            — firm profile & team
                        </li>
                    </ul>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
