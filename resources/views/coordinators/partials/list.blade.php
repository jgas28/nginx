<div class="space-y-4 md:hidden">
    @foreach($data as $deliveryRequest)
        <article class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-slate-900">{{ $deliveryRequest->mtm ?? 'N/A' }}</div>
                    <div class="mt-1 text-xs uppercase tracking-[0.12em] text-slate-500">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</div>
                </div>
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    {{ $deliveryRequest->deliveryStatus->status_name ?? 'N/A' }}
                </span>
            </div>
            <dl class="mt-4 space-y-3 text-sm text-slate-600">
                <div><dt class="font-semibold text-slate-700">Delivery Number</dt><dd>@foreach($deliveryRequest->lineItems as $lineItem){{ $lineItem->delivery_number ?? 'N/A' }}@if(!$loop->last), @endif @endforeach</dd></div>
                <div><dt class="font-semibold text-slate-700">Site ID</dt><dd>@foreach($deliveryRequest->lineItems as $lineItem){{ $lineItem->site_name ?? 'N/A' }}@if(!$loop->last), @endif @endforeach</dd></div>
                <div><dt class="font-semibold text-slate-700">Delivery Address</dt><dd>@foreach($deliveryRequest->lineItems as $lineItem){{ $lineItem->delivery_address ?? 'N/A' }}@if(!$loop->last), @endif @endforeach</dd></div>
                <div><dt class="font-semibold text-slate-700">Truck / Region / Province</dt><dd>{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }} / {{ $deliveryRequest->area->area_code ?? 'N/A' }} / {{ $deliveryRequest->region->province ?? 'N/A' }}</dd></div>
                <div><dt class="font-semibold text-slate-700">Delivery Rate</dt><dd>{{ $deliveryRequest->delivery_rate ?? 'N/A' }}</dd></div>
            </dl>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('coordinators.edit', ['deliveryRequest' => $deliveryRequest->id, 'tab' => request('tab')]) }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm transition hover:-translate-y-0.5 hover:from-amber-100 hover:to-yellow-100 hover:shadow"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-amber-600"><i class="fas fa-pen text-[10px]"></i></span>Edit</a>
                @if($deliveryRequest->delivery_type != 'Regular')
                    <a href="{{ route('coordinators.splitView', $deliveryRequest) }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-lime-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm transition hover:-translate-y-0.5 hover:from-emerald-100 hover:to-lime-100 hover:shadow"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-emerald-600"><i class="fas fa-code-branch text-[10px]"></i></span>Split</a>
                @endif
                <form action="{{ route('coordinators.destroy', $deliveryRequest) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this delivery request?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-gradient-to-r from-rose-50 to-red-50 px-3 py-1.5 text-xs font-semibold text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:from-rose-100 hover:to-red-100 hover:shadow"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-rose-600"><i class="fas fa-trash text-[10px]"></i></span>Delete</button>
                </form>
            </div>
        </article>
    @endforeach
</div>

<div class="hidden w-full overflow-x-auto md:block">
    <table class="min-w-full bg-white border border-gray-200">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 border-b">MTM</th>
                <th class="px-4 py-2 border-b">Company Code</th>
                <th class="px-4 py-2 border-b">Delivery Number</th>
                <th class="px-4 py-2 border-b">Site ID</th>
                <th class="px-4 py-2 border-b">Delivery Address</th>
                <th class="px-4 py-2 border-b">Delivery Rate</th>
                <th class="px-4 py-2 border-b">Truck Type</th>
                <th class="px-4 py-2 border-b">Region</th>
                <th class="px-4 py-2 border-b">Province</th>
                <th class="px-4 py-2 border-b">Status</th>
                <th class="px-4 py-2 border-b">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $deliveryRequest)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 border-b">{{ $deliveryRequest->mtm ?? 'N/A' }}</td>
                    <td class="px-4 py-2 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>
                    <td class="px-4 py-2 border-b">
                        @foreach($deliveryRequest->lineItems as $lineItem)
                            {{ $lineItem->delivery_number ?? 'N/A' }}@if(!$loop->last)/ @endif
                        @endforeach
                    </td>
                    <td class="px-4 py-2 border-b">
                        @foreach($deliveryRequest->lineItems as $lineItem)
                            {{ $lineItem->site_name ?? 'N/A' }}@if(!$loop->last)/ @endif
                        @endforeach
                    </td>
                    <td class="px-4 py-2 border-b">
                        @foreach($deliveryRequest->lineItems as $lineItem)
                            {{ $lineItem->delivery_address ?? 'N/A' }}@if(!$loop->last)/ @endif
                        @endforeach
                    </td>
                    <td class="px-4 py-2 border-b">{{ $deliveryRequest->delivery_rate ?? 'N/A' }}</td>
                    <td class="px-4 py-2 border-b">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
                    <td class="px-4 py-2 border-b">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>
                    <td class="px-4 py-2 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>
                    <td class="px-4 py-2 border-b">{{ $deliveryRequest->deliveryStatus->status_name ?? 'N/A' }}</td>
                    <td class="px-4 py-2 border-b">
                        <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('coordinators.edit', ['deliveryRequest' => $deliveryRequest->id, 'tab' => request('tab')]) }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm transition hover:-translate-y-0.5 hover:from-amber-100 hover:to-yellow-100 hover:shadow"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-amber-600"><i class="fas fa-pen text-[10px]"></i></span>Edit</a>
                        @if($deliveryRequest->delivery_type != 'Regular')
                            <a href="{{ route('coordinators.splitView', $deliveryRequest) }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-lime-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm transition hover:-translate-y-0.5 hover:from-emerald-100 hover:to-lime-100 hover:shadow"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-emerald-600"><i class="fas fa-code-branch text-[10px]"></i></span>Split</a>
                        @endif
                        <form action="{{ route('coordinators.destroy', $deliveryRequest) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this delivery request?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-gradient-to-r from-rose-50 to-red-50 px-3 py-1.5 text-xs font-semibold text-rose-700 shadow-sm transition hover:-translate-y-0.5 hover:from-rose-100 hover:to-red-100 hover:shadow"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-rose-600"><i class="fas fa-trash text-[10px]"></i></span>Delete</button>
                        </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $data->links() }}
</div>
