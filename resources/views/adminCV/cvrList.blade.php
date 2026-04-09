@extends('layouts.app')

@section('title', 'FCZCNYX - Admin Cash Voucher Request List')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="inline-flex items-center gap-2 rounded-full bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700 ring-1 ring-violet-100">
            <i class="fas fa-file-invoice text-sm"></i>
            Admin CVR List
        </div>
        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Admin / RPM Print Queue</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Manage printed admin and RPM cash vouchers faster with compact filters, searchable results, and batch print support.</p>
    </div>

    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('adminCV.cvrList') }}" method="GET" id="admin-cvr-list-filter-form" class="flex items-end justify-between gap-4">
            <div class="min-w-0 flex-1 max-w-[420px]">
                <label for="cvr_type" class="sr-only">Voucher Type</label>
                <select name="cvr_type" id="cvr_type" data-placeholder="All Voucher Types" class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm text-slate-700 shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100">
                    <option value="">All Voucher Types</option>
                    @foreach ($availableTypes as $type)
                        <option value="{{ $type }}" {{ $cvrType === $type ? 'selected' : '' }}>{{ strtoupper($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex shrink-0 items-end justify-end gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-violet-700"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/15"><i class="fas fa-filter text-xs"></i></span>Apply Filters</button>
                <a href="{{ route('adminCV.cvrList') }}" id="admin-cvr-list-filter-reset" class="inline-flex items-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:text-slate-900"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500"><i class="fas fa-rotate-left text-xs"></i></span>Reset</a>
            </div>
        </form>
    </div>

    <div id="admin-cvr-list-table" class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm" data-fast-table data-endpoint="{{ route('adminCV.cvrList', array_filter(['cvr_type' => $cvrType], fn ($value) => $value !== null && $value !== '')) }}" data-base-endpoint="{{ route('adminCV.cvrList') }}" data-search-selector="#admin-cvr-list-search" data-per-page-selector="#admin-cvr-list-per-page" data-pagination-selector=".admin-cvr-list-pagination a">
        @include('adminCV.cvrList_table', ['cashVoucherRequests' => $cashVoucherRequests, 'search' => $search, 'perPage' => $perPage])
    </div>
</div>

<style>
.searchable-select-source{position:absolute;left:-9999px;opacity:0;pointer-events:none}.searchable-select-panel::-webkit-scrollbar{width:6px}.searchable-select-panel::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:9999px}
</style>
@endsection

@section('scripts')
<script>
(() => {
    const form = document.getElementById('admin-cvr-list-filter-form');
    const select = document.getElementById('cvr_type');
    const table = document.getElementById('admin-cvr-list-table');
    const resetLink = document.getElementById('admin-cvr-list-filter-reset');
    if (!form || !select || !table) return;
    const baseEndpoint = table.dataset.baseEndpoint || form.action;
    const syncPrintButtonVisibility = () => {
        const button = document.getElementById('admin-cvr-print-selected');
        const checkboxes = document.querySelectorAll('.admin-cvr-print-checkbox');
        if (button) button.classList.toggle('hidden', !Array.from(checkboxes).some((checkbox) => checkbox.checked));
    };
    const loadFilteredTable = async () => {
        const url = new URL(baseEndpoint, window.location.origin);
        if (select.value) url.searchParams.set('cvr_type', select.value);
        table.dataset.endpoint = url.toString();
        table.classList.add('opacity-60', 'pointer-events-none', 'transition-opacity');
        try {
            const response = await fetch(url.toString(), {headers: {'X-Requested-With': 'XMLHttpRequest','Accept': 'application/json'}});
            const payload = await response.json();
            table.innerHTML = payload.html || '';
            window.history.replaceState({}, '', url.toString());
            syncPrintButtonVisibility();
        } finally {
            table.classList.remove('opacity-60', 'pointer-events-none');
        }
    };
    select.classList.add('searchable-select-source');
    const wrapper = document.createElement('div');
    wrapper.dataset.open = 'false';
    wrapper.className = 'relative w-full max-w-[420px]';
    wrapper.innerHTML = `<button type="button" class="flex w-full items-center gap-3 rounded-2xl border border-slate-300 bg-white px-3 py-3 text-left text-sm text-slate-700 shadow-sm transition hover:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-500"><span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600"><i class="fas fa-layer-group text-sm"></i></span><span class="min-w-0 flex-1 truncate" data-select-label></span><span class="text-slate-400"><i class="fas fa-chevron-down text-xs"></i></span></button><div class="searchable-select-panel absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/10" style="max-width:420px;"><div class="border-b border-slate-200 p-3"><div class="relative"><span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"><i class="fas fa-magnifying-glass text-xs"></i></span><input type="text" placeholder="Search voucher type..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-violet-300 focus:bg-white focus:ring-2 focus:ring-violet-100" data-select-search></div></div><div class="max-h-56 overflow-y-auto p-2" data-select-list></div><div class="hidden px-4 py-3 text-sm text-slate-500" data-select-empty>No matching voucher types found.</div></div>`;
    select.insertAdjacentElement('afterend', wrapper);
    const trigger = wrapper.querySelector('button');
    const panel = wrapper.querySelector('.searchable-select-panel');
    const label = wrapper.querySelector('[data-select-label]');
    const searchInput = wrapper.querySelector('[data-select-search]');
    const list = wrapper.querySelector('[data-select-list]');
    const emptyState = wrapper.querySelector('[data-select-empty]');
    const closePanel = () => { wrapper.dataset.open = 'false'; panel.classList.add('hidden'); };
    const updateLabel = () => { const option = select.options[select.selectedIndex]; label.textContent = option && option.value !== '' ? option.textContent.trim() : 'All Voucher Types'; };
    const renderOptions = (term = '') => {
        const normalized = term.trim().toLowerCase(); list.innerHTML = ''; let visible = 0;
        Array.from(select.options).forEach((option) => {
            if (!option.value && normalized) return;
            if (normalized && !option.textContent.toLowerCase().includes(normalized)) return;
            visible += 1;
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm transition ${option.selected ? 'bg-violet-50 text-violet-700' : 'text-slate-700 hover:bg-slate-100'}`;
            button.innerHTML = `<span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${option.selected ? 'bg-violet-100 text-violet-600' : 'bg-slate-100 text-slate-500'}"><i class="fas fa-layer-group text-xs"></i></span><span class="min-w-0 flex-1 truncate">${option.textContent.trim()}</span>${option.selected ? '<i class="fas fa-check text-xs text-violet-500"></i>' : ''}`;
            button.addEventListener('click', () => { select.value = option.value; updateLabel(); renderOptions(searchInput.value); closePanel(); loadFilteredTable(); });
            list.appendChild(button);
        });
        emptyState.classList.toggle('hidden', visible !== 0);
    };
    trigger.addEventListener('click', () => { const open = wrapper.dataset.open !== 'true'; wrapper.dataset.open = open ? 'true' : 'false'; panel.classList.toggle('hidden', !open); if (open) { searchInput.value = ''; renderOptions(); setTimeout(() => searchInput.focus(), 0); } });
    searchInput.addEventListener('input', () => renderOptions(searchInput.value));
    document.addEventListener('click', (event) => { if (!wrapper.contains(event.target)) closePanel(); });
    form.addEventListener('submit', (event) => { event.preventDefault(); loadFilteredTable(); });
    if (resetLink) resetLink.addEventListener('click', (event) => { event.preventDefault(); select.value = ''; updateLabel(); renderOptions(); loadFilteredTable(); });
    document.addEventListener('change', (event) => {
        if (event.target.id === 'admin-cvr-select-all') document.querySelectorAll('.admin-cvr-print-checkbox').forEach((checkbox) => checkbox.checked = event.target.checked);
        if (event.target.id === 'admin-cvr-select-all' || event.target.classList.contains('admin-cvr-print-checkbox')) syncPrintButtonVisibility();
    });
    document.addEventListener('submit', (event) => {
        if (event.target.id !== 'admin-cvr-print-form') return;
        const formElement = event.target;
        formElement.querySelectorAll('.dynamic-cvr-input').forEach((element) => element.remove());
        document.querySelectorAll('.admin-cvr-print-checkbox:checked').forEach((checkbox) => {
            const requestId = checkbox.dataset.requestId;
            const cashVoucherId = checkbox.dataset.cashVoucherId;
            const cvrType = checkbox.dataset.cvrType;
            const requestInput = document.createElement('input');
            requestInput.type = 'hidden'; requestInput.name = 'request_ids[]'; requestInput.value = requestId; requestInput.className = 'dynamic-cvr-input';
            const cashVoucherInput = document.createElement('input');
            cashVoucherInput.type = 'hidden'; cashVoucherInput.name = `cash_voucher_ids[${requestId}]`; cashVoucherInput.value = cashVoucherId; cashVoucherInput.className = 'dynamic-cvr-input';
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden'; typeInput.name = `cvr_types[${requestId}]`; typeInput.value = cvrType; typeInput.className = 'dynamic-cvr-input';
            formElement.appendChild(requestInput); formElement.appendChild(cashVoucherInput); formElement.appendChild(typeInput);
        });
    });
    table.addEventListener('fast-table:loaded', syncPrintButtonVisibility);
    updateLabel(); renderOptions(); syncPrintButtonVisibility();
})();
</script>
@endsection
