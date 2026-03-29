export default function GuestLayout({ children }) {
    return (
        <div className="min-h-full bg-gradient-to-br from-slate-50 via-brand-50/40 to-teal-50/60">
            <div className="flex min-h-screen flex-col justify-center px-4 py-12 sm:px-6 lg:px-8">
                <div className="sm:mx-auto sm:w-full sm:max-w-md">
                    <div className="mb-8 text-center">
                        <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-brand-700 text-lg font-bold text-white shadow-lg shadow-brand-700/25">
                            D
                        </div>
                        <h1 className="mt-4 text-2xl font-bold tracking-tight text-slate-900">
                            Divinne Accountancy
                        </h1>
                        <p className="mt-1 text-sm text-slate-600">
                            Practice management for modern firms
                        </p>
                    </div>
                    <div className="rounded-2xl bg-white/90 p-8 shadow-soft ring-1 ring-slate-200/60 backdrop-blur-sm">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
