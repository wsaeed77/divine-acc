import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';

function MagnifyingGlassIcon() {
    return (
        <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
        </svg>
    );
}

function formatUkDate(iso) {
    if (!iso) {
        return '—';
    }
    const s = String(iso).slice(0, 10);
    const d = s.split('-');
    if (d.length !== 3) {
        return s;
    }
    return `${d[2]}/${d[1]}/${d[0]}`;
}

function chCompanyUrl(companyNumber) {
    const n = String(companyNumber ?? '').trim();
    return `https://find-and-update.company-information.service.gov.uk/company/${encodeURIComponent(n)}`;
}

/**
 * @param {{ open: boolean, onClose: () => void, onApplyCompaniesHouse: (companyNumber: string, options?: { mainContact?: { first_name?: string, last_name?: string, date_of_birth?: string } }) => Promise<void> }} props
 */
export default function CompaniesHouseSearchModal({ open, onClose, onApplyCompaniesHouse }) {
    const [step, setStep] = useState('search');
    const [q, setQ] = useState('');
    const [loading, setLoading] = useState(false);
    const [items, setItems] = useState([]);
    const [error, setError] = useState(null);
    const [selectedNumber, setSelectedNumber] = useState('');
    const [preview, setPreview] = useState(null);
    const [loadingPreview, setLoadingPreview] = useState(false);
    const [applying, setApplying] = useState(false);

    useEffect(() => {
        if (!open) {
            return;
        }
        setStep('search');
        setQ('');
        setItems([]);
        setError(null);
        setSelectedNumber('');
        setPreview(null);
        setLoading(false);
        setLoadingPreview(false);
        setApplying(false);
    }, [open]);

    useEffect(() => {
        if (!open) {
            return;
        }
        const onKey = (e) => {
            if (e.key === 'Escape') {
                onClose();
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [open, onClose]);

    const runSearch = async () => {
        const term = q.trim();
        if (term.length < 2) {
            setError('Enter at least 2 characters.');
            setItems([]);
            return;
        }
        setLoading(true);
        setError(null);
        setItems([]);
        try {
            const { data } = await window.axios.post('/lookup/companies-house/search', { q: term });
            const list = data.items ?? [];
            setItems(list);
            if (list.length === 0) {
                setError('No companies found.');
            }
        } catch (err) {
            setError(err.response?.data?.message ?? 'Search failed.');
        } finally {
            setLoading(false);
        }
    };

    const loadPreview = async (companyNumber) => {
        const num = String(companyNumber ?? '').trim();
        if (!num) {
            return;
        }
        setSelectedNumber(num);
        setLoadingPreview(true);
        setError(null);
        try {
            const { data } = await window.axios.post('/lookup/companies-house/preview', {
                company_number: num,
            });
            setPreview(data);
            setStep('detail');
        } catch (err) {
            setError(err.response?.data?.message ?? 'Could not load company details.');
        } finally {
            setLoadingPreview(false);
        }
    };

    const applyCompany = async (mainContact) => {
        setApplying(true);
        setError(null);
        try {
            if (mainContact) {
                await onApplyCompaniesHouse(selectedNumber, { mainContact });
            } else {
                await onApplyCompaniesHouse(selectedNumber, {});
            }
        } catch (err) {
            setError(err.response?.data?.message ?? 'Could not apply company details.');
        } finally {
            setApplying(false);
        }
    };

    if (!open || typeof document === 'undefined') {
        return null;
    }

    const co = preview?.company;

    const panel = (
        <div className="fixed inset-0 z-[200]" role="presentation">
            <button
                type="button"
                className="absolute inset-0 block h-full w-full cursor-default border-0 bg-slate-900/50 p-0"
                onClick={onClose}
                aria-label="Close dialog"
            />
            <div className="pointer-events-none fixed inset-0 flex items-start justify-center overflow-y-auto p-4 pt-12 sm:pt-16">
                <div
                    className="pointer-events-auto mt-0 w-full max-w-2xl rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="ch-search-title"
                >
                    <div className="flex items-center justify-between rounded-t-xl bg-slate-900 px-4 py-3">
                        <h2 id="ch-search-title" className="text-base font-semibold text-white">
                            Search Companies House
                        </h2>
                        <button
                            type="button"
                            className="rounded p-1 text-white/90 hover:bg-white/10 hover:text-white"
                            onClick={onClose}
                            aria-label="Close"
                        >
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div className="p-4">
                        {step === 'search' && (
                            <>
                                <div className="flex gap-2">
                                    <input
                                        type="text"
                                        className="input-field min-w-0 flex-1"
                                        placeholder="Company name or company number"
                                        value={q}
                                        onChange={(e) => setQ(e.target.value)}
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') {
                                                e.preventDefault();
                                                runSearch();
                                            }
                                        }}
                                        autoComplete="off"
                                        autoFocus
                                    />
                                    <button
                                        type="button"
                                        className="inline-flex shrink-0 items-center justify-center rounded-lg bg-brand-700 px-4 py-2.5 text-white shadow-sm transition hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50"
                                        disabled={loading}
                                        aria-label="Search"
                                        onClick={() => runSearch()}
                                    >
                                        {loading ? (
                                            <span className="text-sm">…</span>
                                        ) : (
                                            <MagnifyingGlassIcon />
                                        )}
                                    </button>
                                </div>
                                {error && <p className="mt-3 text-sm text-red-600">{error}</p>}
                                {items.length > 0 && (
                                    <ul
                                        className="mt-4 max-h-80 space-y-2 overflow-y-auto rounded-lg border border-slate-200 bg-slate-50 p-2"
                                        role="listbox"
                                        aria-label="Search results"
                                    >
                                        {items.map((row) => (
                                            <li key={row.company_number || row.title}>
                                                <button
                                                    type="button"
                                                    role="option"
                                                    className="group w-full rounded-lg border border-transparent bg-white px-3 py-3 text-left text-sm shadow-sm ring-1 ring-slate-200/80 transition hover:border-brand-600 hover:bg-brand-700 hover:ring-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500 disabled:opacity-60"
                                                    disabled={loadingPreview}
                                                    onClick={() => loadPreview(row.company_number)}
                                                >
                                                    <div className="font-semibold text-slate-900 group-hover:text-white">
                                                        {row.title}
                                                    </div>
                                                    {row.subtitle ? (
                                                        <div className="mt-0.5 text-xs text-slate-600 group-hover:text-white/90">
                                                            {row.subtitle}
                                                        </div>
                                                    ) : null}
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </>
                        )}

                        {step === 'detail' && (
                            <>
                                {loadingPreview ? (
                                    <p className="text-sm text-slate-600">Loading company…</p>
                                ) : co ? (
                                    <>
                                        <h3 className="text-lg font-semibold text-slate-900">{co.company_name}</h3>
                                        <dl className="mt-4 space-y-3 border-t border-slate-200 pt-4 text-sm">
                                            <div className="flex flex-wrap gap-x-4 border-b border-slate-100 py-2">
                                                <dt className="w-40 shrink-0 font-medium text-slate-600">
                                                    Company number
                                                </dt>
                                                <dd className="text-slate-900">{co.company_number}</dd>
                                            </div>
                                            <div className="flex flex-wrap gap-x-4 border-b border-slate-100 py-2">
                                                <dt className="w-40 shrink-0 font-medium text-slate-600">
                                                    Company status
                                                </dt>
                                                <dd className="capitalize text-slate-900">
                                                    {co.company_status
                                                        ? String(co.company_status).replace(/-/g, ' ')
                                                        : '—'}
                                                </dd>
                                            </div>
                                            <div className="flex flex-wrap gap-x-4 border-b border-slate-100 py-2">
                                                <dt className="w-40 shrink-0 font-medium text-slate-600">
                                                    Incorporation date
                                                </dt>
                                                <dd className="text-slate-900">
                                                    {formatUkDate(co.incorporation_date)}
                                                </dd>
                                            </div>
                                            <div className="flex flex-wrap gap-x-4 border-b border-slate-100 py-2">
                                                <dt className="w-40 shrink-0 font-medium text-slate-600">
                                                    Accounts reference date
                                                </dt>
                                                <dd className="text-slate-900">
                                                    {co.accounts_reference_date ?? '—'}
                                                </dd>
                                            </div>
                                            <div className="flex flex-wrap gap-x-4 py-2">
                                                <dt className="w-40 shrink-0 font-medium text-slate-600">
                                                    Current directors
                                                </dt>
                                                <dd className="text-slate-900">
                                                    {co.directors_summary ?? '—'}
                                                </dd>
                                            </div>
                                        </dl>
                                        <div className="mt-6 flex flex-wrap items-center gap-3">
                                            <button
                                                type="button"
                                                className="btn-primary"
                                                disabled={applying}
                                                onClick={() => setStep('directors')}
                                            >
                                                Select company
                                            </button>
                                            <a
                                                href={chCompanyUrl(selectedNumber)}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="btn-secondary inline-flex items-center justify-center"
                                            >
                                                View on Companies House
                                            </a>
                                            <button
                                                type="button"
                                                className="text-sm text-slate-600 underline hover:text-slate-900"
                                                onClick={() => {
                                                    setStep('search');
                                                    setPreview(null);
                                                    setSelectedNumber('');
                                                }}
                                            >
                                                Back to search
                                            </button>
                                        </div>
                                    </>
                                ) : (
                                    <p className="text-sm text-slate-600">No data.</p>
                                )}
                                {error && <p className="mt-3 text-sm text-red-600">{error}</p>}
                            </>
                        )}

                        {step === 'directors' && preview && (
                            <>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    {preview.company?.company_name}
                                </h3>
                                <p className="mt-1 text-sm text-slate-600">
                                    Choose a director to set as the main contact, or apply company details only.
                                </p>
                                <ul className="mt-4 max-h-72 space-y-3 overflow-y-auto border-t border-slate-200 pt-4">
                                    {(preview.directors ?? []).length === 0 ? (
                                        <li className="text-sm text-slate-600">No active directors listed.</li>
                                    ) : (
                                        preview.directors.map((dir, idx) => (
                                            <li
                                                key={`${dir.display_name}-${idx}`}
                                                className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3"
                                            >
                                                <div>
                                                    <div className="font-medium text-slate-900">
                                                        {dir.display_name}
                                                    </div>
                                                    {dir.year_of_birth ? (
                                                        <div className="text-xs text-slate-600">
                                                            Born {dir.year_of_birth}
                                                        </div>
                                                    ) : null}
                                                </div>
                                                <button
                                                    type="button"
                                                    className="shrink-0 rounded-lg bg-brand-700 px-3 py-2 text-sm font-medium text-white hover:bg-brand-800 disabled:opacity-50"
                                                    disabled={applying}
                                                    onClick={() =>
                                                        applyCompany({
                                                            first_name: dir.first_name ?? '',
                                                            last_name: dir.last_name ?? '',
                                                            date_of_birth: dir.date_of_birth ?? '',
                                                        })
                                                    }
                                                >
                                                    Use as main contact
                                                </button>
                                            </li>
                                        ))
                                    )}
                                </ul>
                                <div className="mt-6 flex flex-wrap items-center gap-3 border-t border-slate-200 pt-4">
                                    <button
                                        type="button"
                                        className="btn-secondary"
                                        disabled={applying}
                                        onClick={() => applyCompany(null)}
                                    >
                                        Apply company details only
                                    </button>
                                    <button
                                        type="button"
                                        className="text-sm text-slate-600 underline hover:text-slate-900"
                                        disabled={applying}
                                        onClick={() => setStep('detail')}
                                    >
                                        Back
                                    </button>
                                </div>
                                {error && <p className="mt-3 text-sm text-red-600">{error}</p>}
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );

    return createPortal(panel, document.body);
}
