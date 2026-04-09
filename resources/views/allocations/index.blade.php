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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('allocation-requests-table');
    let debounceTimer = null;

    function bindEvents() {
        const searchInput = document.getElementById('allocation-search');
        const perPageInput = document.getElementById('allocation-per-page');
        const selectAll = document.getElementById('select-all-allocation');
        const checkboxes = table.querySelectorAll('.allocation-select-item');
        const allocateButton = document.getElementById('allocate-selected-button');

        searchInput?.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadTable({
                search: this.value,
                per_page: perPageInput?.value || 10
            }), 250);
        });

        perPageInput?.addEventListener('change', function () {
            loadTable({
                search: searchInput?.value || '',
                per_page: this.value
            });
        });

        table.querySelectorAll('.allocation-pagination a').forEach((link) => {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                loadFromUrl(this.href);
            });
        });

        selectAll?.addEventListener('change', function () {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
            });
            toggleAllocateButton();
        });

        checkboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', function () {
                if (!this.checked && selectAll) {
                    selectAll.checked = false;
                }

                if (selectAll && checkboxes.length > 0 && Array.from(checkboxes).every((item) => item.checked)) {
                    selectAll.checked = true;
                }

                toggleAllocateButton();
            });
        });

        allocateButton?.addEventListener('click', function () {
            const selectedIds = Array.from(table.querySelectorAll('.allocation-select-item:checked')).map((checkbox) => checkbox.value);

            if (!selectedIds.length) {
                return;
            }

            window.location.href = @json(route('allocations.allocate')) + '?ids=' + selectedIds.join(',');
        });

        toggleAllocateButton();
    }

    function toggleAllocateButton() {
        const allocateBar = document.getElementById('allocate-action-bar');
        const countLabel = document.getElementById('allocation-selected-count');
        const checkedCount = table.querySelectorAll('.allocation-select-item:checked').length;

        if (!allocateBar || !countLabel) {
            return;
        }

        allocateBar.classList.toggle('hidden', checkedCount === 0);
        countLabel.textContent = checkedCount;
    }

    function loadTable(params) {
        const url = new URL(@json(route('allocations.index')));
        Object.entries(params).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                url.searchParams.set(key, value);
            }
        });
        loadFromUrl(url.toString());
    }

    function loadFromUrl(url) {
        table.classList.add('opacity-60');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then((response) => response.text())
            .then((html) => {
                table.innerHTML = html;
                table.classList.remove('opacity-60');
                bindEvents();
                window.history.replaceState({}, '', url);
            })
            .catch((error) => {
                table.classList.remove('opacity-60');
                console.error('Error fetching allocation requests:', error);
            });
    }

    bindEvents();
});
</script>
@endsection
