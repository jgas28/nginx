@extends('layouts.app') <!-- Assuming you have a main layout file -->

@section('content')
    <div class="max-w mx-auto p-6">
        <!-- Filter Form -->
        <form action="{{ route('cash-voucher-report') }}" method="GET" class="mb-8 bg-white p-6 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- CVR Number Filter -->
                <div>
                    <label for="cvr_number" class="block text-sm font-medium text-gray-700">CVR Number</label>
                    <input type="text" class="mt-2 p-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500" id="cvr_number" name="cvr_number" value="{{ request('cvr_number') }}">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select class="mt-2 p-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500" id="status" name="status">
                        <option value="">Select Status</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>For Validation</option>
                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>For Collection</option>
                        <option value="4" {{ request('status') == '4' ? 'selected' : '' }}>For Approval</option>
                        <option value="5" {{ request('status') == '5' ? 'selected' : '' }}>Completed</option>
                        <option value="10" {{ request('status') == '10' ? 'selected' : '' }}>Rejected Liquidation</option>
                    </select>
                </div>

                <!-- Request Type Filter -->
                <div>
                    <label for="request_type" class="block text-sm font-medium text-gray-700">Request Type</label>
                    <select class="mt-2 p-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500" id="request_type" name="request_type">
                        <option value="">Select Request Type</option>
                        @foreach($requestTypes as $type)
                            <option value="{{ $type->id }}" {{ request('request_type') == $type->id ? 'selected' : '' }}>{{ $type->request_type }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" class="mt-2 p-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500" id="start_date" name="start_date" value="{{ request('start_date') }}">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" class="mt-2 p-3 border border-gray-300 rounded-lg w-full focus:outline-none focus:ring-2 focus:ring-blue-500" id="end_date" name="end_date" value="{{ request('end_date') }}">
                </div>

                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition duration-200 ease-in-out w-full sm:w-auto">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
        <!-- Data Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg border border-gray-200">
            <table class="min-w-full text-sm text-gray-600">
                <thead class="bg-blue-100">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">CVR Number</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Company Name</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Expense Type</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Supplier ID</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Request Type</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Total Cash</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Total Card</th>
                        <th class="px-6 py-3 text-left font-medium text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $result)
                        <tr class="border-t border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $result->cvr_number_1 }}</td>
                            <td class="px-6 py-4">{{ $result->company_name }}</td>
                            <td class="px-6 py-4">{{ $result->expense_code }}</td>
                            <td class="px-6 py-4">{{ $result->supplier_id }}</td>
                            <td class="px-6 py-4">{{ $result->request_type_name }}</td>
                            <td class="px-6 py-4">{{ number_format($result->total_liquidation_cash, 2) }}</td>
                            <td class="px-6 py-4">{{ number_format($result->total_liquidation_card, 2) }}</td>
                            <td class="px-6 py-4">{{ $result->status_check }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-6">
            <div class="inline-flex rounded-md shadow-sm">
                <!-- Previous Page Button -->
                <a href="{{ $results->previousPageUrl() . '&' . http_build_query(request()->except('page')) }}" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium text-gray-700 bg-white border-gray-300 rounded-l-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $results->onFirstPage() ? 'cursor-not-allowed text-gray-300' : '' }}" {{ $results->onFirstPage() ? 'aria-disabled="true"' : '' }}>
                    &laquo; Previous
                </a>

                <!-- Pagination Numbers -->
                @foreach ($results->getUrlRange(1, $results->lastPage()) as $page => $url)
                    <a href="{{ $url . '&' . http_build_query(request()->except('page')) }}" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $page == $results->currentPage() ? 'bg-blue-600 text-white' : 'text-gray-700 bg-white' }} border-gray-300 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ $page }}
                    </a>
                @endforeach

                <!-- Next Page Button -->
                <a href="{{ $results->nextPageUrl() . '&' . http_build_query(request()->except('page')) }}" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium text-gray-700 bg-white border-gray-300 rounded-r-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $results->hasMorePages() ? '' : 'cursor-not-allowed text-gray-300' }}" {{ $results->hasMorePages() ? '' : 'aria-disabled="true"' }}>
                    Next &raquo;
                </a>
            </div>
        </div>


    </div>
@endsection
