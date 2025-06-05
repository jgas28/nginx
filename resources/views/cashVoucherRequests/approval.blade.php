@extends('layouts.app')

@section('title', 'Cash Voucher Approval')

@section('content')
    <!-- Container to hold both elements in a row -->
    <div class="flex justify-between items-center mb-4">
        <!-- Search Form (aligned to the left) -->
        <form method="GET" action="{{ route('cashVoucherRequests.approval') }}" class="flex items-center flex-grow space-x-4">
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
                    <th class="py-2 px-4 border-b">CVR Number</th>
                    <th class="py-2 px-4 border-b">Amount</th>
                    <th class="py-2 px-4 border-b">Type</th>
                    <th class="py-2 px-4 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryRequests as $deliveryRequest)
                    <tr class="hover:bg-gray-50">
                        <!-- {{$deliveryRequest->id}} -->
                        <td class="py-2 px-4 border-b">{{ $deliveryRequest->mtm }}</td>
                        <td class="py-2 px-4 border-b">{{ $deliveryRequest->cvr_number }}</td>
                        <td class="py-2 px-4 border-b">{{ $deliveryRequest->amount }}</td>
                        <td class="py-2 px-4 border-b">{{ $deliveryRequest->cvr_type }}</td>
                        <td class="py-2 px-4 border-b">
                            <a href="{{ route('cashVoucherRequests.approvalRequest', $deliveryRequest->id) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600" title="Create Cash Voucher">
                                View
                            </a>
                            <a href="{{ route('cashVoucherRequests.editView', ['id' => $deliveryRequest->id]) }}" class="btn btn-success bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600" title="Edit Cash Voucher">
                                Edit
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
        fetch(`{{ route('cashVoucherRequests.approval') }}?search=${searchQuery}`)
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
