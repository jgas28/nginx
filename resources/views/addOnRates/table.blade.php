<div class="border-b border-slate-200 px-4 py-4 sm:px-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2 text-sm text-slate-600"><span>Show</span><select id="add-on-rates-per-page" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">@foreach([5, 10, 25, 50] as $size)<option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>@endforeach</select><span>entries</span></div>
            <div class="text-sm text-slate-500">{{ $addOnRates->total() }} total add on rates</div>
        </div>
        <div class="relative w-full lg:max-w-sm"><div class="pointer-events-none absolute left-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-blue-50 text-blue-600 shadow-sm"><i class="fas fa-search text-sm"></i></div><input type="text" id="add-on-rates-search" value="{{ $search ?? '' }}" placeholder="Search code, name, rate, or delivery type..." class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2.5 pl-14 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"></div>
    </div>
</div>
<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-700">
        <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500"><tr><th class="px-4 py-3 font-semibold sm:px-6">#</th><th class="px-4 py-3 font-semibold sm:px-6">Code</th><th class="px-4 py-3 font-semibold sm:px-6">Add On Rate</th><th class="px-4 py-3 font-semibold sm:px-6">Rate</th><th class="px-4 py-3 font-semibold sm:px-6">% Rate</th><th class="px-4 py-3 font-semibold sm:px-6">Delivery Type</th><th class="px-4 py-3 font-semibold sm:px-6 text-right">Actions</th></tr></thead>
        <tbody class="divide-y divide-slate-200 bg-white">
            @forelse($addOnRates as $addOnRate)
                <tr class="hover:bg-slate-50/80">
                    <td class="px-4 py-3.5 text-sm text-slate-500 sm:px-6">{{ ($addOnRates->firstItem() ?? 0) + $loop->index }}</td>
                    <td class="px-4 py-3.5 sm:px-6"><span class="inline-flex items-center justify-center rounded-full bg-gradient-to-r from-blue-50 to-cyan-100 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">{{ $addOnRate->add_on_rate_type_code }}</span></td>
                    <td class="px-4 py-3.5 font-semibold text-slate-900 sm:px-6"><div class="inline-flex items-center gap-2"><span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-blue-600"><i class="fas fa-tags text-[11px]"></i></span><span>{{ $addOnRate->add_on_rate_type_name }}</span></div></td>
                    <td class="px-4 py-3.5 text-slate-600 sm:px-6">{{ $addOnRate->rate }}</td>
                    <td class="px-4 py-3.5 text-slate-600 sm:px-6">{{ $addOnRate->percent_rate }}</td>
                    <td class="px-4 py-3.5 text-slate-600 sm:px-6">{{ $addOnRate->delivery_type }}</td>
                    <td class="px-4 py-3.5 sm:px-6"><div class="flex flex-wrap items-center justify-end gap-2"><a href="{{ route('addOnRates.edit', $addOnRate) }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm transition hover:-translate-y-0.5 hover:from-amber-100 hover:to-yellow-100 hover:shadow"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-amber-600"><i class="fas fa-pen text-[10px]"></i></span>Edit</a><form action="{{ route('addOnRates.destroy', $addOnRate) }}" method="POST" class="add-on-rate-delete-form">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-gradient-to-r from-rose-50 to-red-50 px-3 py-1.5 text-xs font-semibold text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:from-rose-100 hover:to-red-100 hover:shadow"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-rose-600"><i class="fas fa-trash text-[10px]"></i></span>Delete</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500 sm:px-6"><div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400"><i class="fas fa-tags text-lg"></i></div><p class="mt-3 font-medium text-slate-700">No add on rates found</p><p class="mt-1 text-sm">Try a different search term or add a new record.</p></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-4 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between"><p>Showing <span class="font-semibold text-slate-700">{{ $addOnRates->firstItem() ?? 0 }}</span> to <span class="font-semibold text-slate-700">{{ $addOnRates->lastItem() ?? 0 }}</span> of <span class="font-semibold text-slate-700">{{ $addOnRates->total() }}</span> entries</p><div class="add-on-rates-pagination">{{ $addOnRates->links('pagination::tailwind') }}</div></div>
