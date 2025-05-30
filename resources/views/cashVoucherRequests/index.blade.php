@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
    <!-- Page Header -->
    <h1 class="text-2xl font-semibold mb-4 text-gray-700">Cash Voucher Request</h1>

    <!-- Container to hold both elements in a row -->
    <div class="flex justify-between items-center mb-4">
        <!-- Search Form (aligned to the left) -->
        <form method="GET" action="{{ route('cashVoucherRequests.index') }}" class="flex items-center flex-grow space-x-4">
            <!-- Search Input (Longer Input) -->
            <input type="text" id="search" name="search" value="{{ $search ?? '' }}" class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-2/3 md:w-3/4 lg:w-1/2 xl:w-1/2" placeholder="Search MTM...">
        </form>

        <!-- Create New Employee Button (aligned to the right) -->
        <!-- <a href="{{ route('cashVoucherRequests.create') }}" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 ml-4 whitespace-nowrap">
            Request Cash Voucher
        </a> -->
    </div>

    <!-- Employees Table -->
    <div id="cashVoucherRequests-table" class="bg-white shadow-md rounded-lg overflow-hidden">
        @include('cashVoucherRequests.table', ['deliveryRequests' => $deliveryRequests])  <!-- Pass the correct variable -->
    </div>

@endsection

@section('scripts')

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
        fetch(`{{ route('cashVoucherRequests.index') }}?search=${searchQuery}`)
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
