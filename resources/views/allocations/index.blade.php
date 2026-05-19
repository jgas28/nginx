@extends('layouts.app')

@section('title', 'Allocation Request')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-7xl px-4">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Allocation Request</h1>
                <p class="mt-1 text-sm text-slate-500">Review ready-for-allocation delivery requests in a compact, searchable queue.</p>
            </div>
            <a href="{{ route('allocation.drlist') }}" class="inline-flex items-center justify-center gap-2 self-start rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow sm:self-auto">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-blue-600"><i class="fas fa-list-ul text-xs"></i></span>
                Delivery Request List
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div id="allocation-requests-table">
                @include('allocations.table', ['deliveryRequests' => $deliveryRequests, 'search' => $search, 'perPage' => $perPage])
            </div>
        </div>
    </div>
</div>

{{-- Floating Selection Summary Panel --}}
<div id="selection-panel" class="fixed bottom-0 left-0 right-0 z-50 translate-y-full transition-transform duration-300 ease-in-out">
    <div class="mx-auto max-w-7xl px-4 pb-4">
        <div class="rounded-2xl border border-emerald-200 bg-white shadow-[0_-4px_32px_rgba(0,0,0,0.12)] overflow-hidden">

            {{-- Panel header --}}
            <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-emerald-600 to-teal-600 px-4 py-3">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white">
                        <i class="fas fa-layer-group text-sm"></i>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-white">
                            <span id="panel-count">0</span> Request(s) Selected
                        </p>
                        <p class="text-xs text-emerald-100">Ready for allocation</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="panel-clear-all" type="button"
                        class="inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-white/20">
                        <i class="fas fa-times text-[10px]"></i>
                        Clear All
                    </button>
                    <button id="panel-allocate-btn" type="button"
                        class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-sm font-bold text-emerald-700 shadow-sm transition hover:shadow-md hover:-translate-y-0.5">
                        <i class="fas fa-route text-sm"></i>
                        <span class="hidden sm:inline">Allocate Selected</span>
                        <span class="sm:hidden">Allocate</span>
                    </button>
                </div>
            </div>

            {{-- Selected items chips --}}
            <div class="flex items-start gap-2 overflow-x-auto px-4 py-3 scrollbar-hide" id="panel-chips">
                {{-- chips injected by JS --}}
            </div>
        </div>
    </div>
</div>

<style>
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    .scrollbar-hide::-webkit-scrollbar { display: none; }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table      = document.getElementById('allocation-requests-table');
    const panel      = document.getElementById('selection-panel');
    const panelCount = document.getElementById('panel-count');
    const panelChips = document.getElementById('panel-chips');
    const clearBtn   = document.getElementById('panel-clear-all');
    const allocBtn   = document.getElementById('panel-allocate-btn');

    // Persistent selection state: Map of id -> { id, mtm, company }
    const selected = new Map();

    let debounceTimer = null;

    // ── Panel UI ──────────────────────────────────────────────────────────────
    function updatePanel() {
        const count = selected.size;
        panelCount.textContent = count;

        // Show / hide panel
        if (count > 0) {
            panel.classList.remove('translate-y-full');
        } else {
            panel.classList.add('translate-y-full');
        }

        // Rebuild chips
        panelChips.innerHTML = '';
        selected.forEach(({ id, mtm, company }) => {
            const chip = document.createElement('div');
            chip.className = 'flex shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-slate-50 pl-2 pr-1 py-1 text-xs font-semibold text-slate-700';
            chip.innerHTML = `
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <i class="fas fa-truck text-[9px]"></i>
                </span>
                <span class="max-w-[120px] truncate" title="${mtm}">${mtm}</span>
                <span class="text-slate-400 text-[10px]">${company}</span>
                <button data-remove="${id}" class="chip-remove ml-1 flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition">
                    <i class="fas fa-times text-[9px]"></i>
                </button>
            `;
            panelChips.appendChild(chip);
        });

        // Also update the old action bar count if visible
        const countLabel = document.getElementById('allocation-selected-count');
        const actionBar  = document.getElementById('allocate-action-bar');
        if (countLabel) countLabel.textContent = count;
        if (actionBar)  actionBar.classList.toggle('hidden', count === 0);
    }

    // Remove single chip
    panelChips.addEventListener('click', function (e) {
        const btn = e.target.closest('.chip-remove');
        if (!btn) return;
        const id = btn.dataset.remove;
        selected.delete(id);
        // Uncheck in table if visible
        const cb = table.querySelector(`.allocation-select-item[value="${id}"]`);
        if (cb) cb.checked = false;
        syncSelectAll();
        updatePanel();
    });

    // Clear all
    clearBtn.addEventListener('click', function () {
        selected.clear();
        table.querySelectorAll('.allocation-select-item').forEach(cb => cb.checked = false);
        const sa = document.getElementById('select-all-allocation');
        if (sa) sa.checked = false;
        updatePanel();
    });

    // Allocate
    allocBtn.addEventListener('click', function () {
        if (!selected.size) return;
        window.location.href = @json(route('allocations.allocate')) + '?ids=' + [...selected.keys()].join(',');
    });

    // ── Table binding ─────────────────────────────────────────────────────────
    function syncSelectAll() {
        const sa  = document.getElementById('select-all-allocation');
        const cbs = table.querySelectorAll('.allocation-select-item');
        if (sa && cbs.length > 0) {
            sa.checked = Array.from(cbs).every(cb => cb.checked);
            sa.indeterminate = !sa.checked && Array.from(cbs).some(cb => cb.checked);
        }
    }

    function restoreCheckboxes() {
        table.querySelectorAll('.allocation-select-item').forEach(cb => {
            if (selected.has(cb.value)) {
                cb.checked = true;
            }
        });
        syncSelectAll();
    }

    function bindEvents() {
        const searchInput  = document.getElementById('allocation-search');
        const perPageInput = document.getElementById('allocation-per-page');
        const selectAll    = document.getElementById('select-all-allocation');
        const checkboxes   = table.querySelectorAll('.allocation-select-item');
        const oldAllocBtn  = document.getElementById('allocate-selected-button');

        searchInput?.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadTable({
                search: this.value,
                per_page: perPageInput?.value || 10
            }), 250);
        });

        perPageInput?.addEventListener('change', function () {
            loadTable({ search: searchInput?.value || '', per_page: this.value });
        });

        table.querySelectorAll('.allocation-pagination a').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                loadFromUrl(this.href);
            });
        });

        selectAll?.addEventListener('change', function () {
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                const row = cb.closest('tr');
                if (this.checked) {
                    selected.set(cb.value, {
                        id:      cb.value,
                        mtm:     row?.dataset.mtm     || cb.value,
                        company: row?.dataset.company || ''
                    });
                } else {
                    selected.delete(cb.value);
                }
            });
            updatePanel();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const row = this.closest('tr');
                if (this.checked) {
                    selected.set(this.value, {
                        id:      this.value,
                        mtm:     row?.dataset.mtm     || this.value,
                        company: row?.dataset.company || ''
                    });
                } else {
                    selected.delete(this.value);
                }
                syncSelectAll();
                updatePanel();
            });
        });

        // Keep old inline button in sync (hides when panel exists)
        oldAllocBtn?.addEventListener('click', function () {
            if (!selected.size) return;
            window.location.href = @json(route('allocations.allocate')) + '?ids=' + [...selected.keys()].join(',');
        });

        restoreCheckboxes();
        updatePanel();
    }

    function loadTable(params) {
        const url = new URL(@json(route('allocations.index')));
        Object.entries(params).forEach(([k, v]) => {
            if (v !== null && v !== undefined && v !== '') url.searchParams.set(k, v);
        });
        loadFromUrl(url.toString());
    }

    function loadFromUrl(url) {
        table.classList.add('opacity-60');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                table.innerHTML = html;
                table.classList.remove('opacity-60');
                bindEvents();
                window.history.replaceState({}, '', url);
            })
            .catch(() => table.classList.remove('opacity-60'));
    }

    bindEvents();
});
</script>
@endsection
