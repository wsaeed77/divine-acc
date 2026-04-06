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

export default function Index({ tasks, filters }) {
    const { flash } = usePage().props;

    return (
        <AuthenticatedLayout header="Tasks">
            <Head title="Tasks" />

            {flash?.success && (
                <div className="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-900 ring-1 ring-emerald-100">
                    {flash.success}
                </div>
            )}

            <div className="mb-6 flex flex-wrap items-center gap-3">
                <span className="text-sm text-slate-600">Show:</span>
                <Link
                    href="/tasks?status=open"
                    className={
                        filters.status !== 'completed'
                            ? 'btn-primary text-sm py-1.5 px-3'
                            : 'btn-secondary text-sm py-1.5 px-3'
                    }
                    preserveScroll
                >
                    Open
                </Link>
                <Link
                    href="/tasks?status=completed"
                    className={
                        filters.status === 'completed'
                            ? 'btn-primary text-sm py-1.5 px-3'
                            : 'btn-secondary text-sm py-1.5 px-3'
                    }
                    preserveScroll
                >
                    Completed
                </Link>
                {filters.status !== 'completed' && (
                    <Link
                        href={filters.overdue ? '/tasks?status=open' : '/tasks?status=open&overdue=1'}
                        className={
                            filters.overdue
                                ? 'btn-primary text-sm py-1.5 px-3'
                                : 'btn-secondary text-sm py-1.5 px-3'
                        }
                        preserveScroll
                    >
                        Overdue only
                    </Link>
                )}
            </div>

            <div className="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-slate-200/60">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="px-4 py-3 text-left font-semibold text-slate-700">Task</th>
                                <th className="px-4 py-3 text-left font-semibold text-slate-700">Client</th>
                                <th className="px-4 py-3 text-left font-semibold text-slate-700">Assignee</th>
                                <th className="px-4 py-3 text-left font-semibold text-slate-700">Latest action</th>
                                <th className="px-4 py-3 text-left font-semibold text-slate-700">Target</th>
                                <th className="px-4 py-3 text-left font-semibold text-slate-700">Deadline</th>
                                <th className="px-4 py-3 text-right font-semibold text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {tasks.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="px-4 py-10 text-center text-slate-500">
                                        No tasks yet. Enable services on a client and save — tasks are created
                                        automatically.
                                    </td>
                                </tr>
                            ) : (
                                tasks.data.map((task) => (
                                    <tr
                                        key={task.id}
                                        className={task.is_overdue ? 'bg-amber-50/80' : ''}
                                    >
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-slate-900">{task.task_name}</div>
                                            <div className="mt-1 flex flex-wrap gap-1.5">
                                                {task.is_overdue && (
                                                    <span className="inline-block rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-900">
                                                        Overdue
                                                    </span>
                                                )}
                                                {task.status === 'switched_off' && (
                                                    <span className="inline-block rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-900">
                                                        Service off
                                                    </span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3">
                                            <Link
                                                href={`/clients/${task.client_id}`}
                                                className="text-brand-700 hover:underline"
                                            >
                                                {task.client?.name}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">
                                            {task.assignee?.name ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">
                                            {task.latest_action?.name ?? '—'}
                                        </td>
                                        <td className="px-4 py-3 text-slate-700">{fmtDate(task.target_date)}</td>
                                        <td className="px-4 py-3 text-slate-700">{fmtDate(task.deadline_date)}</td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={`/tasks/${task.id}/edit`}
                                                className="text-sm font-medium text-brand-700 hover:underline"
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
                {tasks.links && tasks.links.length > 3 && (
                    <div className="flex flex-wrap gap-2 border-t border-slate-100 px-4 py-3">
                        {tasks.links.map((link, i) => (
                            <button
                                key={i}
                                type="button"
                                disabled={!link.url}
                                onClick={() => link.url && router.visit(link.url)}
                                className="rounded-lg px-3 py-1 text-sm text-slate-700 hover:bg-slate-100 disabled:opacity-40"
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
