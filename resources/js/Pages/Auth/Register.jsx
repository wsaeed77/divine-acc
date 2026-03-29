import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../Layouts/GuestLayout';

export default function Register() {
    const form = useForm({
        firm_name: '',
        firm_email: '',
        owner_name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        form.post('/register', { preserveScroll: true });
    };

    return (
        <GuestLayout>
            <Head title="Register your firm" />
            <div>
                <h2 className="text-center text-lg font-semibold text-slate-900">
                    Create your workspace
                </h2>
                <p className="mt-1 text-center text-sm text-slate-600">
                    Already registered?{' '}
                    <Link href="/login" className="font-medium text-brand-700 hover:text-brand-800">
                        Sign in
                    </Link>
                </p>

                <form onSubmit={submit} className="mt-8 space-y-5">
                    <div className="rounded-xl bg-brand-50/80 p-4 ring-1 ring-brand-100">
                        <p className="text-xs font-semibold uppercase tracking-wide text-brand-800">
                            Firm details
                        </p>
                        <div className="mt-4 space-y-4">
                            <div>
                                <label htmlFor="firm_name" className="label-field">
                                    Firm name
                                </label>
                                <input
                                    id="firm_name"
                                    type="text"
                                    className="input-field"
                                    value={form.data.firm_name}
                                    onChange={(e) => form.setData('firm_name', e.target.value)}
                                    required
                                />
                                {form.errors.firm_name && (
                                    <p className="mt-1 text-sm text-red-600">{form.errors.firm_name}</p>
                                )}
                            </div>
                            <div>
                                <label htmlFor="firm_email" className="label-field">
                                    Firm email <span className="text-slate-400">(optional)</span>
                                </label>
                                <input
                                    id="firm_email"
                                    type="email"
                                    className="input-field"
                                    value={form.data.firm_email}
                                    onChange={(e) => form.setData('firm_email', e.target.value)}
                                />
                                {form.errors.firm_email && (
                                    <p className="mt-1 text-sm text-red-600">{form.errors.firm_email}</p>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="rounded-xl bg-slate-50/80 p-4 ring-1 ring-slate-100">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-600">
                            Your account (first admin)
                        </p>
                        <div className="mt-4 space-y-4">
                            <div>
                                <label htmlFor="owner_name" className="label-field">
                                    Your name
                                </label>
                                <input
                                    id="owner_name"
                                    type="text"
                                    className="input-field"
                                    autoComplete="name"
                                    value={form.data.owner_name}
                                    onChange={(e) => form.setData('owner_name', e.target.value)}
                                    required
                                />
                                {form.errors.owner_name && (
                                    <p className="mt-1 text-sm text-red-600">{form.errors.owner_name}</p>
                                )}
                            </div>
                            <div>
                                <label htmlFor="email" className="label-field">
                                    Email
                                </label>
                                <input
                                    id="email"
                                    type="email"
                                    className="input-field"
                                    autoComplete="username"
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
                                    className="input-field"
                                    autoComplete="new-password"
                                    value={form.data.password}
                                    onChange={(e) => form.setData('password', e.target.value)}
                                    required
                                />
                                {form.errors.password && (
                                    <p className="mt-1 text-sm text-red-600">{form.errors.password}</p>
                                )}
                            </div>
                            <div>
                                <label htmlFor="password_confirmation" className="label-field">
                                    Confirm password
                                </label>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    className="input-field"
                                    autoComplete="new-password"
                                    value={form.data.password_confirmation}
                                    onChange={(e) =>
                                        form.setData('password_confirmation', e.target.value)
                                    }
                                    required
                                />
                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        className="btn-primary w-full py-3"
                        disabled={form.processing}
                    >
                        {form.processing ? 'Creating workspace…' : 'Create workspace'}
                    </button>
                </form>
            </div>
        </GuestLayout>
    );
}
