<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">MTM</th>
            <th class="py-2 px-4 border-b">Delivery Number</th>
            <th class="py-2 px-4 border-b">Province</th>
            <th class="py-2 px-4 border-b">Site</th>
            <th class="py-2 px-4 border-b">Company Code</th>
            <th class="py-2 px-4 border-b">Status</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($deliveryRequests as $deliveryRequest)  <!-- Make sure the correct variable is used here -->
        <tr class="hover:bg-gray-50">
            <!-- MTM -->
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->mtm }}</td>

            <!-- Delivery Number (List of Line Items) -->
            <td class="py-2 px-4 border-b">
                @foreach($deliveryRequest->lineItems as $lineItem)
                    {{ $lineItem->delivery_number }}@if(!$loop->last)  @endif
                @endforeach
            </td>

            <!-- Region Province -->
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->region->province ?? 'N/A' }}</td>

            <!-- Site -->
            <td class="py-2 px-4 border-b">
                @foreach($deliveryRequest->lineItems as $lineItem)
                    {{ $lineItem->site_name }}@if(!$loop->last)  @endif
                @endforeach
            </td>

            <!-- Company Code -->
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->company->company_code ?? 'N/A' }}</td>

            <!-- Status -->
            <td class="py-2 px-4 border-b">
                @foreach($deliveryRequest->lineItems as $lineItem)
                    {{ $lineItem->deliveryStatus->status_name ?? 'N/A' }}@if(!$loop->last)  @endif
                @endforeach
            </td>

            <!-- Actions (if needed) -->
            <td class="py-2 px-4 border-b">
                <a href="{{ route('cashVoucherRequests.request', $deliveryRequest) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600" title="Create Cash Voucher">
                    Create Cash Voucher
                </a>
                <!-- <form action="" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this delivery request?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600" title="Delete this delivery request">
                        Delete
                    </button>
                </form> -->
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

<!-- Pagination -->
<div class="mt-4">
    {{ $deliveryRequests->links('pagination::tailwind') }}
</div>
