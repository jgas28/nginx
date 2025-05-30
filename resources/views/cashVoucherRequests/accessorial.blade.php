@extends('layouts.app')

@section('title', 'Cash Voucher Accessorial')

@section('content')
    <!-- Page Header -->
    <h1 class="text-2xl font-semibold mb-4 text-gray-700">Cash Voucher Accessorial</h1>

    <!-- Container to hold both elements in a row -->
    <div class="flex justify-between items-center mb-4">
        <!-- Search Form (aligned to the left) -->
        <form method="GET" action="{{ route('cashVoucherRequests.accessorial') }}" class="flex items-center flex-grow space-x-4">
            <!-- Search Input (Longer Input) -->
            <input type="text" id="search" name="search" value="{{ $search ?? '' }}" class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-2/3 md:w-3/4 lg:w-1/2 xl:w-1/2" placeholder="Search MTM...">
        </form>
    </div>

    <!-- Cash Voucher Requests Table -->
    <div id="cashVoucherRequests-table" class="bg-white shadow-md rounded-lg overflow-hidden">
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
            @foreach($deliveryRequests as $deliveryRequest)
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
                        <a href="{{ route('cashVoucherRequests.accessorialRequest', $deliveryRequest) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600" title="Create Cash Voucher">
                            Generate Cash Voucher
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $deliveryRequests->links('pagination::tailwind') }}
        </div>
    </div>

@endsection

@section('scripts')
<!-- Add jQuery from CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Listen for input changes in the search field
    document.getElementById('search').addEventListener('input', function () {
        let searchQuery = this.value;

        // Fetch the filtered delivery requests using AJAX
        fetchcashVoucherRequests(searchQuery);
    });

    // Function to fetch filtered delivery requests via AJAX
    function fetchcashVoucherRequests(searchQuery) {
        // Use the Fetch API to send a GET request with the search query
        fetch(`{{ route('cashVoucherRequests.accessorial') }}?search=${searchQuery}`)
            .then(response => response.text())
            .then(data => {
                // Replace the content of the employee table with the new data
                document.getElementById('cashVoucherRequests-table').innerHTML = data;
            })
            .catch(error => {
                console.error('Error fetching delivery requests:', error);
            });
    }
</script>
@endsection
