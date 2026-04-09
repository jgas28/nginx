<div class="space-y-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between" data-warehouse-toolbar>
        <div class="flex items-center gap-3 text-sm text-slate-600">
            <span>Show</span>
            <select data-warehouse-per-page class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                @foreach([5, 10, 25, 50] as $size)
                    <option value="{{ $size }}" {{ (int) ($perPage ?? 10) === $size ? 'selected' : '' }}>{{ $size }}</option>
                @endforeach
            </select>
            <span>entries</span>
        </div>
        <div class="relative w-full max-w-md">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" value="{{ $search ?? '' }}" data-warehouse-search placeholder="Search code, name, or location..." class="w-full rounded-2xl border border-slate-300 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">
        </div>
    </div>

    <div class="overflow-hidden rounded-[24px] border border-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm text-slate-700">
                <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Code</th>
                        <th class="px-5 py-4">Warehouse Name</th>
                        <th class="px-5 py-4">Location</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($warehouses as $warehouse)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $warehouse->warehouse_code }}</td>
                            <td class="px-5 py-4">{{ $warehouse->warehouse_name }}</td>
                            <td class="px-5 py-4">{{ $warehouse->warehouse_location }}</td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                        <i class="fas fa-pen"></i>
                                        Edit
                                    </a>
                                    <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">No warehouses found for the current search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex flex-col gap-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <p>Showing {{ $warehouses->firstItem() ?? 0 }} to {{ $warehouses->lastItem() ?? 0 }} of {{ $warehouses->total() }} entries</p>
        <div data-warehouse-pagination>
            {{ $warehouses->links('pagination::tailwind') }}
        </div>
    </div>
</div>
