import { Head, Link } from '@inertiajs/react';
import { ArrowRightIcon, CheckCircleIcon } from '@heroicons/react/24/outline';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen bg-gradient-to-b from-white via-slate-50 to-brand-50/40">
                <header className="border-b border-slate-200/80 bg-white/80 backdrop-blur-md">
                    <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                        <div className="flex items-center gap-2">
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-700 text-sm font-bold text-white shadow-lg shadow-brand-700/20">
                                D
                            </div>
                            <span className="text-lg font-semibold text-slate-900">
                                Divinne Accountancy
                            </span>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link
                                href="/login"
                                className="text-sm font-medium text-slate-600 transition hover:text-slate-900"
                            >
                                Sign in
                            </Link>
                            <Link href="/register" className="btn-primary px-5 py-2 text-sm">
                                Start free trial
                            </Link>
                        </div>
                    </div>
                </header>

                <main>
                    <section className="mx-auto max-w-7xl px-4 pb-20 pt-16 sm:px-6 lg:flex lg:items-center lg:gap-16 lg:px-8 lg:pt-24">
                        <div className="max-w-xl lg:max-w-lg">
                            <p className="text-sm font-semibold uppercase tracking-wide text-brand-700">
                                SaaS for UK accounting firms
                            </p>
                            <h1 className="mt-3 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                                Run your practice with clarity
                            </h1>
                            <p className="mt-6 text-lg leading-relaxed text-slate-600">
                                Clients, compliance, tasks, and team workflows in one calm,
                                modern workspace — inspired by the tools you already trust.
                            </p>
                            <div className="mt-10 flex flex-wrap gap-4">
                                <Link
                                    href="/register"
                                    className="btn-primary inline-flex items-center gap-2 px-6 py-3 text-base"
                                >
                                    Create your workspace
                                    <ArrowRightIcon className="h-5 w-5" />
                                </Link>
                                <Link
                                    href="/login"
                                    className="btn-secondary px-6 py-3 text-base"
                                >
                                    Sign in
                                </Link>
                            </div>
                            <ul className="mt-12 space-y-3 text-sm text-slate-600">
                                {[
                                    'Multi-tenant workspaces per firm',
                                    'Client & compliance records aligned to UK practice',
                                    'Task workflows driven by services you offer',
                                ].map((item) => (
                                    <li key={item} className="flex items-start gap-2">
                                        <CheckCircleIcon className="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
                                        <span>{item}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>

                        <div className="mt-16 flex-1 lg:mt-0">
                            <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-800 via-brand-700 to-teal-600 p-1 shadow-2xl shadow-brand-900/20">
                                <div className="rounded-[1.35rem] bg-slate-950/20 p-8 backdrop-blur-sm">
                                    <div className="rounded-2xl bg-white p-6 shadow-xl">
                                        <div className="flex items-center justify-between border-b border-slate-100 pb-4">
                                            <span className="text-sm font-semibold text-slate-900">
                                                Dashboard preview
                                            </span>
                                            <span className="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-800">
                                                Phase 1
                                            </span>
                                        </div>
                                        <div className="mt-4 space-y-3">
                                            <div className="h-3 w-3/4 rounded bg-slate-100" />
                                            <div className="h-3 w-full rounded bg-slate-100" />
                                            <div className="h-3 w-5/6 rounded bg-slate-100" />
                                            <div className="mt-6 grid grid-cols-2 gap-3">
                                                <div className="rounded-xl bg-brand-50 p-4 ring-1 ring-brand-100">
                                                    <p className="text-xs font-medium text-brand-800">
                                                        Clients
                                                    </p>
                                                    <p className="mt-1 text-2xl font-bold text-brand-900">
                                                        —
                                                    </p>
                                                </div>
                                                <div className="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-100">
                                                    <p className="text-xs font-medium text-slate-600">
                                                        Tasks due
                                                    </p>
                                                    <p className="mt-1 text-2xl font-bold text-slate-900">
                                                        —
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                <footer className="border-t border-slate-200 bg-white/80 py-8 text-center text-sm text-slate-500">
                    © {new Date().getFullYear()} Divinne Accountancy
                </footer>
            </div>
        </>
    );
}
