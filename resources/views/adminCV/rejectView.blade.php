@extends('layouts.app')

@section('title', 'Rejected Approval')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-6 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 ring-1 ring-rose-100">
                    <i class="fas fa-ban text-sm"></i>
                    Rejected Voucher Queue
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">Rejected Cash Vouchers</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review rejected admin and RPM vouchers with faster search, compact actions, and batch printing.
                </p>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div id="admin-reject-table"
             data-fast-table
             data-endpoint="{{ route('adminCV.rejectView', ['search' => $search ?? '', 'per_page' => $perPage ?? 10]) }}"
             data-base-endpoint="{{ route('adminCV.rejectView') }}"
             data-search-selector="#admin-reject-search"
             data-per-page-selector="#admin-reject-per-page"
             data-pagination-selector=".admin-reject-pagination a">
            @include('adminCV.partials.rejectView-table', [
                'cashVouchers' => $cashVouchers,
                'search' => $search ?? '',
                'perPage' => $perPage ?? 10,
            ])
        </div>
    </div>
</div>

<script>
    (() => {
        const root = document.getElementById('admin-reject-table');
        if (!root) {
            return;
        }

        function syncBulkPrintState(scope = root) {
            const form = scope.querySelector('#admin-reject-multiple-print-form');
            const printBtn = scope.querySelector('#admin-reject-print-selected-btn');
            const selectAll = scope.querySelector('#admin-reject-select-all');
            const checkboxes = Array.from(scope.querySelectorAll('.admin-reject-voucher-checkbox'));

            if (!form || !printBtn || !selectAll) {
                return;
            }

            const toggle = () => {
                const anyChecked = checkboxes.some((checkbox) => checkbox.checked);
                printBtn.classList.toggle('hidden', !anyChecked);
            };

            selectAll.addEventListener('change', () => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                toggle();
            });

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    if (!checkbox.checked) {
                        selectAll.checked = false;
                    } else if (checkboxes.length && checkboxes.every((item) => item.checked)) {
                        selectAll.checked = true;
                    }
                    toggle();
                });
            });

            toggle();
        }

        syncBulkPrintState(root);
        root.addEventListener('fast-table:loaded', () => syncBulkPrintState(root));
    })();
</script>
@endsection
