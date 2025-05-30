@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
    <!-- Page Header -->
    <h1 class="text-2xl font-semibold mb-4 text-gray-700">Add-On Rates</h1>

    <!-- Container to hold both elements in a row -->
    <div class="flex justify-between items-center mb-4">
        <!-- Search Form (aligned to the left) -->
        <form method="GET" action="{{ route('addOnRates.index') }}" class="flex items-center flex-grow space-x-4">
            <!-- Search Input (Longer Input) -->
            <input type="text" id="search" name="search" value="{{ $search ?? '' }}" class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-2/3 md:w-3/4 lg:w-1/2 xl:w-1/2" placeholder="Search add-on rates...">
        </form>

        <!-- Create New Add-On Rate Button (aligned to the right) -->
        <a href="{{ route('addOnRates.create') }}" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 ml-4 whitespace-nowrap">
            Create New Add-On Rate
        </a>
    </div>

    <!-- Add-On Rates Table -->
    <div id="add-on-rates-table" class="bg-white shadow-md rounded-lg overflow-hidden">
        @include('addOnRates.table', ['addOnRates' => $addOnRates])
    </div>

@endsection

@section('scripts')
    <!-- Add jQuery from CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Listen for input changes in the search field
        document.getElementById('search').addEventListener('input', function () {
            let searchQuery = this.value;

            // Fetch the filtered add-on rates
            fetchAddOnRates(searchQuery);
        });

        // Function to fetch add-on rates using AJAX
        function fetchAddOnRates(searchQuery) {
            // Use the Fetch API to send a GET request with the search query
            fetch(`{{ route('addOnRates.index') }}?search=${searchQuery}`)
                .then(response => response.text())
                .then(data => {
                    // Replace the content of the add-on rates table with the new data
                    document.getElementById('add-on-rates-table').innerHTML = data;
                })
                .catch(error => {
                    console.error('Error fetching add-on rates:', error);
                });
        }
    </script>
@endsection
