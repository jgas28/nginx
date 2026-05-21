@php
    $overviewCards = [
        ['label' => 'For Approval', 'value' => number_format($overview['total'] ?? 0), 'icon' => 'fa-stamp', 'bg' => 'from-violet-100 to-fuchsia-100', 'text' => 'text-violet-700'],
        ['label' => 'Admin', 'value' => number_format($overview['admin'] ?? 0), 'icon' => 'fa-user-shield', 'bg' => 'from-blue-100 to-indigo-100', 'text' => 'text-blue-700'],
        ['label' => 'RPM', 'value' => number_format($overview['rpm'] ?? 0), 'icon' => 'fa-gas-pump', 'bg' => 'from-amber-100 to-yellow-100', 'text' => 'text-amber-700'],
        ['label' => 'Delivery Related', 'value' => number_format($overview['delivery_related'] ?? 0), 'icon' => 'fa-truck-fast', 'bg' => 'from-cyan-100 to-sky-100', 'text' => 'text-cyan-700'],
    ];
@endphp

<div class="space-y-5 p-5">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($overviewCards as $card)
            <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-white to-slate-50 px-4 py-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $card['bg'] }} {{ $card['text'] }} ring-1 ring-white shadow-sm">
                        <i class="fas {{ $card['icon'] }} text-sm"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $card['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ $card['value'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-[24px] border border-slate-200">
        <div class="space-y-3 p-4 md:hidden">
            @forelse ($liquidations as $liquidation)
                @php
                    $cashVoucher = optional($liquidation->cashVoucher);
                    $cvrType = $cashVoucher->cvr_type ?? null;
                    $cvrNumber = preg_replace('/\/\d+$/', '', $cashVoucher->cvr_number ?? 'N/A');
                    $details = '';

                    if ($cvrType === 'rpm') {
                        $truckName = optional($cashVoucher->trucks)->truck_name ?? 'N/A';
                        $companyCode = optional($cashVoucher->company)->company_code ?? 'N/A';
                        $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                        $details = "-$truckName-$companyCode$expenseCodeVal";
                    } elseif ($cvrType === 'admin') {
                        $companyCode = optional($cashVoucher->company)->company_code ?? 'N/A';
                        $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                        $details = "-$companyCode$expenseCodeVal";
                    } elseif (in_array($cvrType, ['delivery', 'pullout', 'accessorial', 'freight', 'others'])) {
                        $truckName = optional(optional($liquidation->allocation)->truck)->truck_name ?? 'N/A';
                        $companyCode = optional(optional($liquidation->deliveryRequest)->company)->company_code ?? 'N/A';
                        $expenseCodeVal = optional(optional($liquidation->deliveryRequest)->expenseType)->expense_code ?? 'N/A';
                        $details = "-$truckName-$companyCode$expenseCodeVal";
                    }

                    $typeStyle = match (strtolower((string) $cvrType)) {
                        'admin' => 'bg-blue-50 text-blue-700 ring-blue-100',
                        'rpm' => 'bg-amber-50 text-amber-700 ring-amber-100',
                        'delivery', 'pullout', 'accessorial', 'freight', 'others' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
                        default => 'bg-slate-100 text-slate-700 ring-slate-200',
                    };
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">CVR Number</p>
                            <p class="mt-1 break-words text-sm font-semibold text-slate-900">{{ $cvrNumber }}{!! $details !!}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ optional($cashVoucher->company)->company_name ?? optional(optional($cashVoucher->deliveryRequest)->company)->company_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Voucher Type</p>
                            <span class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $typeStyle }}">
                                {{ strtoupper($cvrType ?? 'N/A') }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Prepared By</p>
                                <p class="mt-1 text-sm text-slate-700">{{ trim(($liquidation->preparedBy->fname ?? '') . ' ' . ($liquidation->preparedBy->lname ?? '')) ?: 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Noted By</p>
                                <p class="mt-1 text-sm text-slate-700">{{ trim(($liquidation->notedBy->fname ?? '') . ' ' . ($liquidation->notedBy->lname ?? '')) ?: 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Created</p>
                                <p class="mt-1 text-sm text-slate-500">{{ optional($liquidation->created_at)->format('Y-m-d') ?? 'N/A' }}</p>
                            </div>
                            <a href="{{ route('liquidations.approval', $liquidation->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-sm font-semibold shadow-md transition sm:w-auto" style="background-color:#7c3aed;border-color:#6d28d9;color:#ffffff;">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full" style="background-color:#ffffff;color:#6d28d9;">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </span>
                                <span style="color:#ffffff;">Review</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-10 text-center text-sm text-slate-500">
                    No liquidations found.
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="px-6 py-4">CVR Number</th>
                        <th class="px-6 py-4">Voucher Type</th>
                        <th class="px-6 py-4">Prepared / Noted By</th>
                        <th class="px-6 py-4">Created</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($liquidations as $liquidation)
                        @php
                            $cashVoucher = optional($liquidation->cashVoucher);
                            $cvrType = $cashVoucher->cvr_type ?? null;
                            $cvrNumber = preg_replace('/\/\d+$/', '', $cashVoucher->cvr_number ?? 'N/A');
                            $details = '';

                            if ($cvrType === 'rpm') {
                                $truckName = optional($cashVoucher->trucks)->truck_name ?? 'N/A';
                                $companyCode = optional($cashVoucher->company)->company_code ?? 'N/A';
                                $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                                $details = "-$truckName-$companyCode$expenseCodeVal";
                            } elseif ($cvrType === 'admin') {
                                $companyCode = optional($cashVoucher->company)->company_code ?? 'N/A';
                                $expenseCodeVal = optional($cashVoucher->expenseTypes)->expense_code ?? 'N/A';
                                $details = "-$companyCode$expenseCodeVal";
                            } elseif (in_array($cvrType, ['delivery', 'pullout', 'accessorial', 'freight', 'others'])) {
                                $truckName = optional(optional($liquidation->allocation)->truck)->truck_name ?? 'N/A';
                                $companyCode = optional(optional($liquidation->deliveryRequest)->company)->company_code ?? 'N/A';
                                $expenseCodeVal = optional(optional($liquidation->deliveryRequest)->expenseType)->expense_code ?? 'N/A';
                                $details = "-$truckName-$companyCode$expenseCodeVal";
                            }

                            $typeStyle = match (strtolower((string) $cvrType)) {
                                'admin' => 'bg-blue-50 text-blue-700 ring-blue-100',
                                'rpm' => 'bg-amber-50 text-amber-700 ring-amber-100',
                                'delivery', 'pullout', 'accessorial', 'freight', 'others' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
                                default => 'bg-slate-100 text-slate-700 ring-slate-200',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900">{{ $cvrNumber }}{!! $details !!}</div>
                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ optional($cashVoucher->company)->company_name ?? optional(optional($cashVoucher->deliveryRequest)->company)->company_name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $typeStyle }}">
                                    {{ strtoupper($cvrType ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm text-slate-700">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                            <i class="fas fa-user-pen text-xs"></i>
                                        </span>
                                        <span>{{ trim(($liquidation->preparedBy->fname ?? '') . ' ' . ($liquidation->preparedBy->lname ?? '')) ?: 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-slate-700">
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-violet-50 text-violet-600">
                                            <i class="fas fa-user-check text-xs"></i>
                                        </span>
                                        <span>{{ trim(($liquidation->notedBy->fname ?? '') . ' ' . ($liquidation->notedBy->lname ?? '')) ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top text-slate-500">
                                {{ optional($liquidation->created_at)->format('Y-m-d') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="flex justify-end">
                                    <a href="{{ route('liquidations.approval', $liquidation->id) }}" class="inline-flex min-w-[140px] items-center justify-center gap-2 rounded-2xl border px-4 py-2.5 text-xs font-semibold shadow-md transition" style="background-color:#7c3aed;border-color:#6d28d9;color:#ffffff;">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full" style="background-color:#ffffff;color:#6d28d9;">
                                            <i class="fas fa-eye text-[11px]"></i>
                                        </span>
                                        <span style="color:#ffffff;">Review</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <i class="fas fa-folder-open text-lg"></i>
                                </div>
                                <p class="mt-3 font-medium text-slate-700">No liquidations found</p>
                                <p class="mt-1 text-sm">Try a different search term or voucher type.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 text-sm text-slate-500 lg:flex-row lg:items-center lg:justify-between">
            <p>
                Showing
                <span class="font-semibold text-slate-700">{{ $liquidations->firstItem() ?? 0 }}</span>
                to
                <span class="font-semibold text-slate-700">{{ $liquidations->lastItem() ?? 0 }}</span>
                of
                <span class="font-semibold text-slate-700">{{ $liquidations->total() }}</span>
                entries
            </p>
            <div class="liquidations-approval-pagination">
                {{ $liquidations->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
