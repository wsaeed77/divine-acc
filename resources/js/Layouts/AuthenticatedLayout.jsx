import { Link, usePage, router } from '@inertiajs/react';
import { Menu, Transition } from '@headlessui/react';
import { Fragment } from 'react';
import {
    ChevronDownIcon,
    ArrowRightOnRectangleIcon,
    Squares2X2Icon,
    Cog6ToothIcon,
    BuildingOffice2Icon,
    ClipboardDocumentListIcon,
} from '@heroicons/react/24/outline';

function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

export default function AuthenticatedLayout({ children, header }) {
    const { auth, tenant } = usePage().props;
    const primary = tenant?.primary_color || '#0f766e';

    const logout = (e) => {
        e.preventDefault();
        router.post('/logout');
    };

    return (
        <div className="min-h-screen bg-slate-50">
            <header
                className="border-b border-slate-200/80 bg-white/90 shadow-card backdrop-blur-sm"
                style={{ borderBottomColor: `${primary}22` }}
            >
                <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <div className="flex min-w-0 items-center gap-3">
                        {tenant?.logo_url ? (
                            <img
                                src={tenant.logo_url}
                                alt=""
                                className="h-9 w-9 rounded-lg object-cover ring-1 ring-slate-200"
                            />
                        ) : (
                            <div
                                className="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold text-white shadow-md"
                                style={{ backgroundColor: primary }}
                            >
                                {tenant?.name?.charAt(0) ?? 'D'}
                            </div>
                        )}
                        <div className="min-w-0">
                            <p className="truncate text-sm font-semibold text-slate-900">
                                {tenant?.name ?? 'Your firm'}
                            </p>
                            <p className="truncate text-xs text-slate-500">
                                Divinne · Practice workspace
                            </p>
                        </div>
                    </div>

                    <nav className="flex flex-wrap items-center gap-1">
                        <Link
                            href="/dashboard"
                            className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                        >
                            <Squares2X2Icon className="h-5 w-5 text-brand-700" />
                            Dashboard
                        </Link>
                        <Link
                            href="/clients"
                            className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                        >
                            <BuildingOffice2Icon className="h-5 w-5 text-brand-700" />
                            Clients
                        </Link>
                        <Link
                            href="/tasks"
                            className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                        >
                            <ClipboardDocumentListIcon className="h-5 w-5 text-brand-700" />
                            Tasks
                        </Link>
                        <Link
                            href="/settings"
                            className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                        >
                            <Cog6ToothIcon className="h-5 w-5 text-brand-700" />
                            Settings
                        </Link>
                    </nav>

                    <Menu as="div" className="relative">
                        <Menu.Button className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-2 text-left text-sm shadow-sm transition hover:border-slate-300">
                            <span
                                className="flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold text-white"
                                style={{ backgroundColor: primary }}
                            >
                                {auth.user?.name
                                    ?.split(' ')
                                    .map((n) => n[0])
                                    .join('')
                                    .slice(0, 2)
                                    .toUpperCase() ?? '?'}
                            </span>
                            <span className="hidden max-w-[10rem] truncate sm:block">
                                {auth.user?.name}
                            </span>
                            <ChevronDownIcon className="h-4 w-4 text-slate-400" />
                        </Menu.Button>
                        <Transition
                            as={Fragment}
                            enter="transition ease-out duration-100"
                            enterFrom="transform opacity-0 scale-95"
                            enterTo="transform opacity-100 scale-100"
                            leave="transition ease-in duration-75"
                            leaveFrom="transform opacity-100 scale-100"
                            leaveTo="transform opacity-0 scale-95"
                        >
                            <Menu.Items className="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none">
                                <div className="border-b border-slate-100 px-4 py-3">
                                    <p className="text-xs text-slate-500">Signed in as</p>
                                    <p className="truncate text-sm font-medium text-slate-900">
                                        {auth.user?.email}
                                    </p>
                                </div>
                                <Menu.Item>
                                    {({ active }) => (
                                        <button
                                            type="button"
                                            onClick={logout}
                                            className={classNames(
                                                active ? 'bg-slate-50' : '',
                                                'flex w-full items-center gap-2 px-4 py-2.5 text-sm text-slate-700'
                                            )}
                                        >
                                            <ArrowRightOnRectangleIcon className="h-5 w-5 text-slate-400" />
                                            Sign out
                                        </button>
                                    )}
                                </Menu.Item>
                            </Menu.Items>
                        </Transition>
                    </Menu>
                </div>
            </header>

            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {header && (
                    <div className="mb-8">
                        <h1 className="text-2xl font-bold tracking-tight text-slate-900">
                            {header}
                        </h1>
                    </div>
                )}
                {children}
            </main>
        </div>
    );
}
