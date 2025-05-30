<!-- resources/views/employees/table.blade.php -->
<div id="allocate-container" class="mt-4 hidden">
    <button id="allocate-button" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        Allocate Selected
    </button>
</div>
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">
                <input type="checkbox" id="select-all">
            </th>
            <th class="py-2 px-4 border-b">MTM</th>
            <th class="py-2 px-4 border-b">MOS</th>
            <th class="py-2 px-4 border-b">Delivery Number</th>
            <th class="py-2 px-4 border-b">Truck Type</th>
            <th class="py-2 px-4 border-b">Delivery Rate</th>
            <th class="py-2 px-4 border-b">Region</th>
            <th class="py-2 px-4 border-b">Province</th>
            <!-- <th class="py-2 px-4 border-b">Site</th> -->
            <th class="py-2 px-4 border-b">Company Code</th>
            <!-- <th class="py-2 px-4 border-b">Statu1s</th> -->
            <th class="py-2 px-4 border-b">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($deliveryRequests as $deliveryRequest)
        <tr class="hover:bg-gray-50">
            <td class="py-2 px-4 border-b">
                <input type="checkbox" class="select-item" value="{{ $deliveryRequest->id }}">
            </td>
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
            <td class="py-2 px-4 border-b">{{ $deliveryRequest->delivery_rate }}</td>
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
        </tr>
    @endforeach
    </tbody>
</table>

<!-- Pagination -->
<div class="mt-4">
    {{ $deliveryRequests->links('pagination::tailwind') }}
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.select-item');
        const allocateContainer = document.getElementById('allocate-container');

        // Toggle all checkboxes when #select-all is toggled
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            toggleAllocateButton();
        });

        // Listen for individual checkbox changes
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                // If any checkbox is unchecked, uncheck the master
                if (!this.checked) {
                    selectAll.checked = false;
                }
                // If all are checked, check the master
                else if (document.querySelectorAll('.select-item:checked').length === checkboxes.length) {
                    selectAll.checked = true;
                }
                toggleAllocateButton();
            });
        });

        function toggleAllocateButton() {
            const checkedCount = document.querySelectorAll('.select-item:checked').length;
            if (checkedCount > 0) {
                allocateContainer.classList.remove('hidden');
            } else {
                allocateContainer.classList.add('hidden');
            }
        }

        // Allocate button click
        document.getElementById('allocate-button').addEventListener('click', function () {
            const selectedIds = Array.from(document.querySelectorAll('.select-item:checked'))
                .map(cb => cb.value);

            if (selectedIds.length === 0) {
                return;
            }

            // Redirect to the allocation view with selected IDs as query string
            const url = "{{ route('allocations.allocate') }}?ids=" + selectedIds.join(',');
            window.location.href = url;
        });
    });
</script>


