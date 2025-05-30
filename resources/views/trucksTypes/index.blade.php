@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
    <!-- Container to hold both elements in a row -->
    <div class="flex justify-between items-center mb-4">
        <!-- Search Form (aligned to the left) -->
        <form method="GET" action="{{ route('trucksTypes.index') }}" class="flex items-center flex-grow space-x-4">
            <!-- Search Input (Longer Input) -->
            <input type="text" id="search" name="search" value="{{ $search ?? '' }}" class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-2/3 md:w-3/4 lg:w-1/2 xl:w-1/2" placeholder="Search Truck Type...">
        </form>

        <!-- Create New Trucks Button (aligned to the right) -->
        <a href="{{ route('trucksTypes.create') }}" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 ml-4 whitespace-nowrap">
            Create New Truck Type
        </a>


        <!-- <a type="button" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 ml-4 whitespace-nowrap" id="btnCreate" onlick="alert(test)">
            Create New trucks
        </a> -->
    </div>

    <!-- trucks Table -->
    <div id="trucks-table" class="bg-white shadow-md rounded-lg overflow-hidden">
        @include('trucksTypes.table', ['trucks' => $trucks])
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

        // Fetch the filtered trucks
        fetchTrucks(searchQuery);
    });

    // Function to fetch trucks using AJAX
    function fetchTrucks(searchQuery) {
        // Use the Fetch API to send a GET request with the search query
        fetch(`{{ route('trucksTypes.index') }}?search=${searchQuery}`)
            .then(response => response.text())
            .then(data => {
                // Replace the content of the trucks table with the new data
                document.getElementById('trucks-table').innerHTML = data;
            })
            .catch(error => {
                console.error('Error fetching trucks:', error);
            });
    }
</script>

@endsection
