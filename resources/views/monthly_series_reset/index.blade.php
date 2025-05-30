@extends('layouts.app')

@section('title', 'Reset Series Numbers')

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-6">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 border border-green-300">
                {{ session('success') }}
            </div>
        @endif

        <h3 class="text-xl font-semibold mb-4">Company Series for {{ \Carbon\Carbon::now()->format('F Y') }}</h3>

        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-300 rounded">
                <thead class="bg-gray-100 text-left text-sm font-semibold text-gray-700">
                    <tr>
                        <th class="px-4 py-2">Company Code</th>
                        <th class="px-4 py-2">Company Name</th>
                        <th class="px-4 py-2">Company Location</th>
                        <th class="px-4 py-2">Running Series</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm">
                    @foreach($series as $record)
                        <tr>
                            <td class="px-4 py-2">{{ $record->company->company_code ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $record->company->company_name ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $record->company->company_location ?? 'N/A' }}</td>
                            <td class="px-4 py-2">{{ $record->series_number }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form action="{{ route('monthly-series.reset') }}" method="POST" onsubmit="return confirm('Are you sure you want to reset all series numbers to 0?')">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition">
                Reset Series Numbers
            </button>
        </form>
    </div>
@endsection
