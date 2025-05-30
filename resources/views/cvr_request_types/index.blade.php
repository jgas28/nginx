@extends('layouts.app')

@section('title', 'FCZCNYX')

@section('content')
    <h1 class="text-2xl font-semibold mb-4 text-gray-700">Request Type</h1>

    <div class="flex justify-between items-center mb-4">
        <!-- Search Form -->
        <form method="GET" action="{{ route('cvr_request_types.index') }}" class="flex items-center flex-grow space-x-4">
            <input type="text" id="search" name="search" value="{{ $search ?? '' }}" class="px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-2/3 md:w-3/4 lg:w-1/2 xl:w-1/2" placeholder="Search Request Type...">
        </form>

        <!-- Create New Request Type Button -->
        <a href="{{ route('cvr_request_types.create') }}" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 ml-4 whitespace-nowrap">
            Create Request Type
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success mb-4 bg-green-100 text-green-800 p-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <div id="cvr_request_types-table" class="bg-white shadow-md rounded-lg overflow-hidden">
        @include('cvr_request_types.table', ['cvr_request_types' => $cvr_request_types])
    </div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.getElementById('search').addEventListener('input', function () {
        let searchQuery = this.value;
        fetchCVR_request_types(searchQuery);
    });

    function fetchCVR_request_types(searchQuery) {
        fetch(`{{ route('cvr_request_types.index') }}?search=${searchQuery}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById('cvr_request_types-table').innerHTML = data;
            })
            .catch(error => console.error('Error fetching request type:', error));
    }
</script>
@endsection
