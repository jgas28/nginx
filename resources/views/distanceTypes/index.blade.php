@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
    <!-- Page Header -->
    <h1 class="text-2xl font-semibold mb-4 text-gray-700">Distance Type</h1>

    <!-- Container to hold both elements in a row -->
    <div class="flex justify-between items-center mb-4">
        <!-- Search Form (aligned to the left) -->
        <form method="GET" action="{{ route('distanceTypes.index') }}" class="flex items-center flex-grow space-x-4">
            <!-- Search Input (Longer Input) -->
            <input type="text" id="search" name="search" value="{{ $search ?? '' }}" class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-2/3 md:w-3/4 lg:w-1/2 xl:w-1/2" placeholder="Search Distance Types...">
        </form>

        <!-- Create New Distance Type Button (aligned to the right) -->
        <a href="{{ route('distanceTypes.create') }}" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 ml-4 whitespace-nowrap">
            Create New Distance Type
        </a>


        <!-- <a type="button" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 ml-4 whitespace-nowrap" id="btnCreate" onlick="alert(test)">
            Create New distanceTypes
        </a> -->
    </div>

    <!-- Success Message -->
    @if (session('success'))
        <div class="alert alert-success mb-4 bg-green-100 text-green-800 p-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <!-- Distance Type Table -->
    <div id="distanceTypes-table" class="bg-white shadow-md rounded-lg overflow-hidden">
        @include('distanceTypes.table', ['distanceTypes' => $distanceType])
    </div>

@endsection

@section('scripts')


<!-- Add jQuery from CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

document.getElementById('btnCreate').addEventListener('click', function () {

});

    // Listen for input changes in the search field
    document.getElementById('search-input').addEventListener('input', function () {
        let searchQuery = this.value;

        // Fetch the filtered distanceTypes
        fetchdistanceTypes(searchQuery);
    });

    // Function to fetch distanceTypes using AJAX
    function fetchdistanceTypes(searchQuery) {
        // Use the Fetch API to send a GET request with the search query
        fetch(`{{ route('distanceTypes.index') }}?search=${searchQuery}`)
            .then(response => response.text())
            .then(data => {
                // Replace the content of the distanceTypes table with the new data
                document.getElementById('distanceTypes-table').innerHTML = data;
            })
            .catch(error => {
                console.error('Error fetching distanceTypes:', error);
            });
    }
</script>

@endsection
