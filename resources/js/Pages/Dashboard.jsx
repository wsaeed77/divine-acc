import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '../Layouts/AuthenticatedLayout';
import { BuildingOffice2Icon, ExclamationTriangleIcon, SparklesIcon } from '@heroicons/react/24/outline';

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

export default function Dashboard({ overdueCount = 0, overdueTasks = [] }) {
    return (
        <AuthenticatedLayout header="Dashboard">
            <Head title="Dashboard" />
            <div className="grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-2 space-y-6">
                    {overdueCount > 0 && (
                        <div className="overflow-hidden rounded-2xl bg-amber-50 shadow-soft ring-1 ring-amber-200/80">
                            <div className="border-b border-amber-100 bg-amber-100/60 px-6 py-4">
                                <div className="flex items-center gap-3">
                                    <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white shadow-md">
                                        <ExclamationTriangleIcon className="h-6 w-6" />
                                    </div>
                                    <div>
                                        <h2 className="text-base font-semibold text-amber-950">
                                            Overdue tasks ({overdueCount})
                                        </h2>
                                        <p className="text-sm text-amber-900/90">
                                            Deadlines have passed; latest action may still show incomplete.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <ul className="divide-y divide-amber-100/80 px-6 py-2">
                                {overdueTasks.map((task) => (
                                    <li key={task.id} className="flex flex-wrap items-center justify-between gap-2 py-3">
                                        <div>
                                            <Link
                                                href={`/tasks/${task.id}/edit`}
                                                className="font-medium text-amber-950 hover:underline"
                                            >
                                                {task.task_name}
                                            </Link>
                                            <div className="text-sm text-amber-900/80">
                                                {task.client?.name}
                                                {task.assignee && (
                                                    <span className="text-amber-800/70">
                                                        {' '}
                                                        · {task.assignee.name}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        <div className="text-sm font-medium text-amber-900">
                                            Due {fmtDate(task.deadline_date)}
                                        </div>
                                    </li>
                                ))}
                            </ul>
                            <div className="border-t border-amber-100 bg-white/60 px-6 py-3">
                                <Link
                                    href="/tasks?overdue=1"
                                    className="text-sm font-medium text-amber-900 underline decoration-amber-400 hover:text-amber-950"
                                >
                                    View all overdue tasks
                                </Link>
                            </div>
                        </div>
                    )}

                    <div className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                        <div className="border-b border-slate-100 bg-gradient-to-r from-brand-50 to-white px-6 py-5">
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700 text-white shadow-md">
                                    <SparklesIcon className="h-6 w-6" />
                                </div>
                                <div>
                                    <h2 className="text-base font-semibold text-slate-900">Workspace overview</h2>
                                    <p className="text-sm text-slate-600">
                                        Clients, tasks, and compliance — see requirements in{' '}
                                        <code className="rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-800">
                                            docs/requirements/task-management.md
                                        </code>
                                        .
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div className="px-6 py-8">
                            <p className="text-sm leading-relaxed text-slate-600">
                                Your firm has its own isolated workspace. Use the sidebar for clients and tasks;
                                overdue work surfaces above when applicable.
                            </p>
                            <ul className="mt-6 space-y-3 text-sm text-slate-600">
                                <li className="flex gap-2">
                                    <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" />
                                    Multi-tenant data (each accounting firm is a tenant)
                                </li>
                                <li className="flex gap-2">
                                    <span className="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500" />
                                    Task list highlights overdue items and supports an overdue-only filter
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
                            <Link href="/clients" className="font-medium text-brand-700 hover:text-brand-800">
                                Clients
                            </Link>{' '}
                            — firm client list & company details
                        </li>
                        <li>
                            <Link href="/tasks" className="font-medium text-brand-700 hover:text-brand-800">
                                Tasks
                            </Link>{' '}
                            — open & completed; filter overdue
                        </li>
                        <li>
                            <Link href="/settings" className="font-medium text-brand-700 hover:text-brand-800">
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
