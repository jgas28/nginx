<div id="lineItemsModal{{ $request->id }}" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded-lg w-1/2 max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h5 class="text-xl font-semibold">Line Items for {{ $request->mtm }}</h5>
            <button class="text-gray-500" onclick="closeModal('{{ $request->id }}')">&times;</button>
        </div>
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-sm font-medium text-gray-500">Warehouse</th>
                    <th class="px-4 py-2 text-sm font-medium text-gray-500">Site Name</th>
                    <th class="px-4 py-2 text-sm font-medium text-gray-500">Delivery Number</th>
                    <th class="px-4 py-2 text-sm font-medium text-gray-500">Accessorial Type</th>
                    <th class="px-4 py-2 text-sm font-medium text-gray-500">Accessorial Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($request->lineItems as $lineItem)
                <tr>
                    <td class="px-4 py-2">{{ $lineItem->warehouse_id }}</td>
                    <td class="px-4 py-2">{{ $lineItem->site_name }}</td>
                    <td class="px-4 py-2">{{ $lineItem->delivery_number }}</td>
                    <td class="px-4 py-2">{{ $lineItem->accessorialType->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $lineItem->accessorial_rate }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
