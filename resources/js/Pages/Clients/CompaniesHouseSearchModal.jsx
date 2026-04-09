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

/**
 * @param {{ open: boolean, onClose: () => void, onSelectCompanyNumber: (companyNumber: string) => Promise<void> }} props
 */
export default function CompaniesHouseSearchModal({ open, onClose, onSelectCompanyNumber }) {
    const [q, setQ] = useState('');
    const [loading, setLoading] = useState(false);
    const [items, setItems] = useState([]);
    const [error, setError] = useState(null);
    const [applyingNumber, setApplyingNumber] = useState(null);

    useEffect(() => {
        if (!open) {
            return;
        }
        setQ('');
        setItems([]);
        setError(null);
        setApplyingNumber(null);
        setLoading(false);
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

    const select = async (companyNumber) => {
        setApplyingNumber(companyNumber);
        setError(null);
        try {
            await onSelectCompanyNumber(companyNumber);
        } catch (err) {
            setError(err.response?.data?.message ?? 'Could not load company details.');
        } finally {
            setApplyingNumber(null);
        }
    };

    if (!open || typeof document === 'undefined') {
        return null;
    }

    const panel = (
        <div className="fixed inset-0 z-[200]" role="presentation">
            {/* Backdrop only — clicks on the card never hit this layer */}
            <button
                type="button"
                className="absolute inset-0 block h-full w-full cursor-default border-0 bg-slate-900/50 p-0"
                onClick={onClose}
                aria-label="Close dialog"
            />
            <div className="pointer-events-none fixed inset-0 flex items-start justify-center overflow-y-auto p-4 pt-12 sm:pt-20">
                <div
                    className="pointer-events-auto mt-0 w-full max-w-lg rounded-xl bg-white shadow-xl ring-1 ring-slate-200"
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
                                            disabled={applyingNumber !== null}
                                            onClick={() => select(row.company_number)}
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
                    </div>
                </div>
            </div>
        </div>
    );

    return createPortal(panel, document.body);
}
