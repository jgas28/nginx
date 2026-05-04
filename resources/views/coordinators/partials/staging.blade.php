<div class="w-full overflow-x-auto">    
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
                    <td class="px-4 py-2 border-b">
                        {{ $deliveryRequest->deliveryStatus->status_name ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-2 border-b">
                        <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('coordinators.edit', ['deliveryRequest' => $deliveryRequest->id, 'tab' => request('tab')]) }}"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-yellow-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm transition hover:-translate-y-0.5 hover:from-amber-100 hover:to-yellow-100 hover:shadow">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/90 text-amber-600"><i class="fas fa-pen text-[10px]"></i></span>Edit
                        </a>
                        <!-- @if($deliveryRequest->delivery_type != 'Regular')
                            <a href="{{ route('coordinators.splitView', $deliveryRequest) }}" class="text-green-600 hover:underline">Split</a>
                        @endif
                        <form action="{{ route('coordinators.destroy', $deliveryRequest) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this delivery request?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form> -->
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $data->links() }}
</div>
