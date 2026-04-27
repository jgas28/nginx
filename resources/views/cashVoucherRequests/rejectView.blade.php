@extends('layouts.app')

@section('title', 'Rejected Approval')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 py-6 sm:py-8">
    <div class="rounded-[28px] border border-slate-200 bg-white px-5 py-6 shadow-sm sm:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 ring-1 ring-rose-100">
                    <i class="fas fa-octagon-xmark text-sm"></i>
                    Rejected Delivery CVRs
                </div>
                <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Rejected Cash Vouchers</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Review rejected delivery-related cash vouchers with faster search, compact actions, and batch printing.
                </p>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
        <div id="cash-voucher-reject-table"
             data-fast-table
             data-endpoint="{{ route('cashVoucherRequests.rejectView', ['search' => $search ?? '', 'per_page' => $perPage ?? 10]) }}"
             data-base-endpoint="{{ route('cashVoucherRequests.rejectView') }}"
             data-search-selector="#cash-voucher-reject-search"
             data-per-page-selector="#cash-voucher-reject-per-page"
             data-pagination-selector=".cash-voucher-reject-pagination a">
            @include('cashVoucherRequests.partials.rejectView-table', [
                'cashVouchers' => $cashVouchers,
                'search' => $search ?? '',
                'perPage' => $perPage ?? 10,
            ])
        </div>
    </div>
</div>

<script>
    (() => {
        const root = document.getElementById('cash-voucher-reject-table');
        if (!root) {
            return;
        }

        function syncBulkPrintState(scope = root) {
            const form = scope.querySelector('#cash-voucher-reject-batch-form');
            const printBtn = scope.querySelector('#cash-voucher-reject-batch-btn');
            const selectAll = scope.querySelector('#cash-voucher-reject-select-all');
            const checkboxes = Array.from(scope.querySelectorAll('.cash-voucher-reject-checkbox'));

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
