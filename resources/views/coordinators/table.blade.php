<!-- resources/views/employees/table.blade.php -->
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">MTM</th>
            <th class="py-2 px-4 border-b">MOS</th>
            <th class="py-2 px-4 border-b">Delivery Number</th>
            <th class="py-2 px-4 border-b">Truck Type</th>
            <th class="py-2 px-4 border-b">Region</th>
            <th class="py-2 px-4 border-b">Province</th>
            <!-- <th class="py-2 px-4 border-b">Site</th> -->
            <th class="py-2 px-4 border-b">Company Code</th>
            <!-- <th class="py-2 px-4 border-b">Statu1s</th> -->
            <th class="py-2 px-4 border-b">Status</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($deliveryRequests as $deliveryRequest)
        <tr class="hover:bg-gray-50">
            <!-- MTM -->
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->mtm }}</td>
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->delivery_date }}</td>

            <!-- Delivery Number (List of Line Items) -->
            <td class="py-2 px-4 border-b">
                @foreach($deliveryRequest->lineItems as $lineItem)
                    {{ $lineItem->delivery_number }}@if(!$loop->last)  @endif
                @endforeach
            </td>

            <td class="py-2 px-4 border-b">{{ $deliveryRequest->truckType->truck_code ?? 'N/A' }}</td>
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->area->area_code ?? 'N/A' }}</td>

            <!-- Region Province -->
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>

            <!-- Site -->
            <!-- <td class="py-2 px-4 border-b">
                @foreach($deliveryRequest->lineItems as $lineItem)
                    {{ $lineItem->site_name }}@if(!$loop->last)  @endif
                @endforeach
            </td> -->

            <!-- Company Code -->
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>

             <!-- Status -->
             <!-- <td class="py-2 px-4 border-b">
                @foreach($deliveryRequest->lineItems as $lineItem)
                    {{ $lineItem->status ?? 'N/A' }}@if(!$loop->last)  @endif
                @endforeach
            </td> -->

            <!-- Status -->
            <td class="py-2 px-4 border-b">
                @foreach($deliveryRequest->lineItems as $lineItem)
                    {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}@if(!$loop->last)  @endif
                @endforeach
            </td>

            <!-- Actions (if needed) -->
            <td class="py-2 px-4 border-b">
                <a href="{{ route('coordinators.edit', $deliveryRequest) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600" title="Edit this delivery request">
                    Edit
                </a>
                @if($deliveryRequest->delivery_type  != 'Regular')
                <a href="{{ route('coordinators.splitView', $deliveryRequest) }}" class="btn btn-success bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600" title="Edit this delivery request">
                    Split
                </a>
                @endif
                <form action="{{ route('coordinators.destroy', $deliveryRequest) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this delivery request?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" title="Delete this delivery request">
                        Delete
                    </button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- Pagination -->
<div class="mt-4">
    {{ $deliveryRequests->links('pagination::tailwind') }}
</div>
