@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
    <!-- Container to hold both elements in a row -->
    <div class="flex justify-between items-center mb-4">
        <!-- Search Form (aligned to the left) -->
        <form method="GET" action="{{ route('companies.index') }}" class="flex items-center flex-grow space-x-4">
            <!-- Search Input (Longer Input) -->
            <input type="text" id="search" name="search" value="{{ $search ?? '' }}" class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-2/3 md:w-3/4 lg:w-1/2 xl:w-1/2" placeholder="Search company...">
        </form>

        <!-- Create New companies Button (aligned to the right) -->
        <a href="{{ route('companies.create') }}" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 ml-4 whitespace-nowrap">
            Create New Comapny
        </a>
    </div>

    <!-- companies Table -->
    <div id="companies-table" class="bg-white shadow-md rounded-lg overflow-hidden">
        @include('companies.table', ['companies' => $companies])
    </div>

@endsection

@section('scripts')
<script>
    // Listen for input changes in the search field
    document.getElementById('search-input').addEventListener('input', function () {
        let searchQuery = this.value;

        // Fetch the filtered companies
        fetchCompanies(searchQuery);
    });

    // Function to fetch companies using AJAX
    function fetchCompanies(searchQuery) {
        // Use the Fetch API to send a GET request with the search query
        fetch(`{{ route('companies.index') }}?search=${searchQuery}`)
            .then(response => response.text())
            .then(data => {
                // Replace the content of the companies table with the new data
                document.getElementById('companies-table').innerHTML = data;
            })
            .catch(error => {
                console.error('Error fetching companies:', error);
            });
    }
</script>

@endsection
