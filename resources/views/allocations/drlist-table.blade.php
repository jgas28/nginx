<div class="border-b border-slate-200 px-4 py-4 sm:px-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-600">
                <i class="fas fa-list-check text-xs text-blue-600"></i>
                {{ $drList->total() }} total delivery requests
            </div>
            <div class="text-sm text-slate-500">Showing delivery rate and accessorial totals in one place.</div>
        </div>
        <div class="text-sm text-slate-500">
            {{ $drList->firstItem() ?? 0 }} - {{ $drList->lastItem() ?? 0 }} of {{ $drList->total() }}
        </div>
    </div>
</div>

<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
            <tr>
                <th class="px-4 py-3 font-semibold sm:px-6">MTM</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Company</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Area / Region</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Delivery Date</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Created</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Created By</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Amounts</th>
                <th class="px-4 py-3 font-semibold sm:px-6">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @forelse ($drList as $dr)
                <tr class="hover:bg-slate-50/80">
                    <td class="px-4 py-3.5 sm:px-6">
                        <div class="flex flex-col gap-1">
                            <span class="font-semibold text-slate-900">{{ $dr->mtm }}</span>
                            <span class="text-xs text-slate-500">DR #{{ $dr->id }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <div class="inline-flex items-center gap-2">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-blue-600">
                                <i class="fas fa-building text-[11px]"></i>
                            </span>
                            <div>
                                <p class="font-semibold text-slate-900">{{ $dr->company->company_name ?? 'N/A' }}</p>
                                <p class="text-xs text-slate-500">{{ $dr->company->company_code ?? 'No code' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <div class="flex flex-col gap-1">
                            <span>{{ $dr->area->area_code ?? 'N/A' }}</span>
                            <span class="text-xs text-slate-500">{{ $dr->region->province ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">{{ \Carbon\Carbon::parse($dr->delivery_date)->format('M d, Y') }}</td>
                    <td class="px-4 py-3.5 sm:px-6">{{ \Carbon\Carbon::parse($dr->created_at)->format('M d, Y') }}</td>
                    <td class="px-4 py-3.5 sm:px-6">{{ $dr->creator_name }}</td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <div class="flex flex-col gap-1">
                            <span class="inline-flex items-center rounded-full bg-gradient-to-r from-emerald-50 to-teal-100 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">
                                Delivery: PHP {{ number_format((float) $dr->delivery_rate, 2) }}
                            </span>
                            <span class="inline-flex items-center rounded-full bg-gradient-to-r from-amber-50 to-yellow-100 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100">
                                Accessorial: PHP {{ number_format((float) $dr->accessorial_total, 2) }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 sm:px-6">
                        <button type="button" data-dr-view="{{ $dr->id }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-indigo-50 px-3 py-1.5 text-xs font-semibold text-blue-700 shadow-sm transition hover:-translate-y-0.5 hover:from-blue-100 hover:to-indigo-100 hover:shadow">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-blue-600">
                                <i class="fas fa-eye text-[10px]"></i>
                            </span>
                            View
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-slate-500 sm:px-6">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <i class="fas fa-list-check text-lg"></i>
                        </div>
                        <p class="mt-3 font-medium text-slate-700">No delivery requests found</p>
                        <p class="mt-1 text-sm">Try changing the filters or reset the list.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
    <p>Showing <span class="font-semibold text-slate-700">{{ $drList->firstItem() ?? 0 }}</span> to <span class="font-semibold text-slate-700">{{ $drList->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-700">{{ $drList->total() }}</span> entries</p>
    <div class="allocation-drlist-pagination">
        {{ $drList->links('pagination::tailwind') }}
    </div>
</div>
