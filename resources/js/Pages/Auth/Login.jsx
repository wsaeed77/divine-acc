import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';

export default function Login({ status }) {
    const form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        form.post('/login', { preserveScroll: true });
    };

    return (
        <GuestLayout>
            <Head title="Sign in" />
            <div>
                <h2 className="text-center text-lg font-semibold text-slate-900">
                    Sign in to your workspace
                </h2>
                <p className="mt-1 text-center text-sm text-slate-600">
                    Or{' '}
                    <Link
                        href="/register"
                        className="font-medium text-brand-700 hover:text-brand-800"
                    >
                        register your firm
                    </Link>
                </p>

                {status && (
                    <div className="mt-4 rounded-lg bg-emerald-50 px-3 py-2 text-center text-sm text-emerald-800 ring-1 ring-emerald-100">
                        {status}
                    </div>
                )}

                <form onSubmit={submit} className="mt-8 space-y-5">
                    <div>
                        <label htmlFor="email" className="label-field">
                            Email
                        </label>
                        <input
                            id="email"
                            type="email"
                            autoComplete="username"
                            className="input-field"
                            value={form.data.email}
                            onChange={(e) => form.setData('email', e.target.value)}
                            required
                        />
                        {form.errors.email && (
                            <p className="mt-1 text-sm text-red-600">{form.errors.email}</p>
                        )}
                    </div>
                    <div>
                        <label htmlFor="password" className="label-field">
                            Password
                        </label>
                        <input
                            id="password"
                            type="password"
                            autoComplete="current-password"
                            className="input-field"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            required
                        />
                        {form.errors.password && (
                            <p className="mt-1 text-sm text-red-600">{form.errors.password}</p>
                        )}
                    </div>
                    <div className="flex items-center justify-between">
                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                className="rounded border-slate-300 text-brand-600 focus:ring-brand-500"
                                checked={form.data.remember}
                                onChange={(e) => form.setData('remember', e.target.checked)}
                            />
                            <span className="text-sm text-slate-600">Remember me</span>
                        </label>
                    </div>
                    <button
                        type="submit"
                        className="btn-primary w-full py-3"
                        disabled={form.processing}
                    >
                        {form.processing ? 'Signing in…' : 'Sign in'}
                    </button>
                </form>
            </div>
        </GuestLayout>
    );
}
