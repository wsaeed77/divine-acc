import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout';

export default function Edit({ task, actionStatuses, breakdownTemplates, userOptions }) {
    const { flash } = usePage().props;

    const form = useForm({
        task_name: task.task_name ?? '',
        assignee_id: task.assignee_id != null ? String(task.assignee_id) : '',
        monitor_id: task.monitor_id != null ? String(task.monitor_id) : '',
        notify_user_id: task.notify_user_id != null ? String(task.notify_user_id) : '',
        latest_action_id: task.latest_action_id != null ? String(task.latest_action_id) : '',
        latest_action_date: task.latest_action_date
            ? String(task.latest_action_date).slice(0, 10)
            : '',
        target_date: task.target_date ? String(task.target_date).slice(0, 10) : '',
        target_date_manual: !!task.target_date_manual,
        deadline_date: task.deadline_date ? String(task.deadline_date).slice(0, 10) : '',
        time_estimate: task.time_estimate != null ? String(task.time_estimate) : '',
        progress_notes: task.progress_notes ?? '',
        description: task.description ?? '',
        breakdown_template_id:
            task.breakdown_template_id != null ? String(task.breakdown_template_id) : '',
        is_favourite: !!task.is_favourite,
    });

    const submit = (e) => {
        e.preventDefault();
        form.put(`/tasks/${task.id}`, { preserveScroll: true });
    };

    const complete = () => {
        if (!confirm('Mark this task as complete?')) {
            return;
        }
        router.post(`/tasks/${task.id}/complete`);
    };

    const destroy = () => {
        if (!confirm('Delete this task permanently?')) {
            return;
        }
        router.delete(`/tasks/${task.id}`);
    };

    const c = task.client;

    return (
        <AuthenticatedLayout header="Edit task">
            <Head title={`Task · ${task.task_name}`} />

            {flash?.success && (
                <div className="mb-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-900 ring-1 ring-emerald-100">
                    {flash.success}
                </div>
            )}

            {task.status === 'switched_off' && (
                <div className="mb-6 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-950 ring-1 ring-amber-200">
                    This task is flagged because the related client service was switched off. You can delete it when
                    no longer needed.
                </div>
            )}

            <div className="mb-6 flex flex-wrap gap-3">
                <Link href="/tasks" className="btn-secondary">
                    Back to tasks
                </Link>
                {task.status !== 'completed' && (
                    <button type="button" onClick={complete} className="btn-primary">
                        Mark complete
                    </button>
                )}
                <button type="button" onClick={destroy} className="btn-secondary text-red-700 ring-red-200 hover:bg-red-50">
                    Delete task
                </button>
            </div>

            <form onSubmit={submit} className="grid gap-8 lg:grid-cols-2">
                <div className="space-y-6">
                    <section className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60">
                        <h2 className="text-sm font-semibold text-slate-900">Client</h2>
                        <dl className="mt-4 space-y-2 text-sm">
                            <div>
                                <dt className="text-slate-500">Name</dt>
                                <dd>
                                    <Link href={`/clients/${c.id}`} className="font-medium text-brand-700 hover:underline">
                                        {c.name}
                                    </Link>
                                </dd>
                            </div>
                            <div>
                                <dt className="text-slate-500">Type</dt>
                                <dd className="text-slate-900">{task.task_type?.name ?? '—'}</dd>
                            </div>
                        </dl>
                    </section>

                    <section className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60">
                        <h2 className="text-sm font-semibold text-slate-900">Status &amp; notes</h2>
                        <div className="mt-4 space-y-4">
                            <div>
                                <label className="label-field">Time estimate (hours)</label>
                                <input
                                    type="number"
                                    step="0.25"
                                    min="0"
                                    className="input-field"
                                    value={form.data.time_estimate}
                                    onChange={(e) => form.setData('time_estimate', e.target.value)}
                                />
                            </div>
                            <div>
                                <label className="label-field">Progress notes</label>
                                <textarea
                                    rows={4}
                                    className="input-field"
                                    value={form.data.progress_notes}
                                    onChange={(e) => form.setData('progress_notes', e.target.value)}
                                />
                            </div>
                        </div>
                    </section>
                </div>

                <div className="space-y-6">
                    <section className="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-slate-200/60">
                        <h2 className="text-sm font-semibold text-slate-900">Details</h2>
                        <div className="mt-4 space-y-4">
                            <div>
                                <label className="label-field">
                                    Task name <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    className="input-field"
                                    value={form.data.task_name}
                                    onChange={(e) => form.setData('task_name', e.target.value)}
                                    required
                                />
                                {form.errors.task_name && (
                                    <p className="mt-1 text-sm text-red-600">{form.errors.task_name}</p>
                                )}
                            </div>
                            <div>
                                <label className="label-field">Assign to</label>
                                <select
                                    className="input-field"
                                    value={form.data.assignee_id}
                                    onChange={(e) => form.setData('assignee_id', e.target.value)}
                                >
                                    <option value="">—</option>
                                    {userOptions.map((u) => (
                                        <option key={u.id} value={u.id}>
                                            {u.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="label-field">Notify on progress</label>
                                <select
                                    className="input-field"
                                    value={form.data.notify_user_id}
                                    onChange={(e) => form.setData('notify_user_id', e.target.value)}
                                >
                                    <option value="">—</option>
                                    {userOptions.map((u) => (
                                        <option key={u.id} value={u.id}>
                                            {u.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="label-field">Assign monitor to</label>
                                <select
                                    className="input-field"
                                    value={form.data.monitor_id}
                                    onChange={(e) => form.setData('monitor_id', e.target.value)}
                                >
                                    <option value="">—</option>
                                    {userOptions.map((u) => (
                                        <option key={u.id} value={u.id}>
                                            {u.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    checked={form.data.target_date_manual}
                                    onChange={(e) => form.setData('target_date_manual', e.target.checked)}
                                />
                                <span className="text-sm text-slate-700">Manually set target date</span>
                            </label>
                            <div>
                                <label className="label-field">Target date</label>
                                <input
                                    type="date"
                                    className="input-field"
                                    value={form.data.target_date || ''}
                                    onChange={(e) => form.setData('target_date', e.target.value)}
                                />
                            </div>
                            <div>
                                <label className="label-field">Deadline</label>
                                <input
                                    type="date"
                                    className="input-field"
                                    value={form.data.deadline_date || ''}
                                    onChange={(e) => form.setData('deadline_date', e.target.value)}
                                />
                            </div>
                            <div>
                                <label className="label-field">Latest action</label>
                                <select
                                    className="input-field"
                                    value={form.data.latest_action_id}
                                    onChange={(e) => form.setData('latest_action_id', e.target.value)}
                                >
                                    <option value="">—</option>
                                    {actionStatuses.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="label-field">Latest action date</label>
                                <input
                                    type="date"
                                    className="input-field"
                                    value={form.data.latest_action_date || ''}
                                    onChange={(e) => form.setData('latest_action_date', e.target.value)}
                                />
                            </div>
                            <div>
                                <label className="label-field">Breakdown template</label>
                                <select
                                    className="input-field"
                                    value={form.data.breakdown_template_id}
                                    onChange={(e) => form.setData('breakdown_template_id', e.target.value)}
                                >
                                    <option value="">—</option>
                                    {breakdownTemplates.map((t) => (
                                        <option key={t.id} value={t.id}>
                                            {t.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                    checked={form.data.is_favourite}
                                    onChange={(e) => form.setData('is_favourite', e.target.checked)}
                                />
                                <span className="text-sm text-slate-700">Favourite</span>
                            </label>
                            <div>
                                <label className="label-field">Description</label>
                                <textarea
                                    rows={4}
                                    className="input-field"
                                    value={form.data.description}
                                    onChange={(e) => form.setData('description', e.target.value)}
                                />
                            </div>
                        </div>
                    </section>

                    <div className="flex justify-end">
                        <button type="submit" className="btn-primary" disabled={form.processing}>
                            {form.processing ? 'Saving…' : 'Save changes'}
                        </button>
                    </div>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
