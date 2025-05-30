@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">

    <h1 class="text-2xl font-bold mb-6">Liquidations Review List</h1>

    <table class="min-w-full border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-3 border">CVR Number</th>
                <th class="p-3 border">Prepared By</th>
                <th class="p-3 border">Noted By</th>
                <th class="p-3 border">Date Created</th>
                <th class="p-3 border">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($liquidations as $liquidation)
                <tr class="hover:bg-gray-50">
                    <td class="p-3 border">{{ $liquidation->cvr_number ?? 'N/A' }}</td>
                    <td class="p-3 border">{{ $liquidation->preparedBy->fname ?? '' }} {{ $liquidation->preparedBy->lname ?? '' }}</td>
                    <td class="p-3 border">{{ $liquidation->notedBy->fname ?? '' }} {{ $liquidation->notedBy->lname ?? '' }}</td>
                    <td class="p-3 border">{{ $liquidation->created_at->format('Y-m-d') }}</td>
                    <td class="p-3 border">
                        <a href="{{ route('liquidations.review', $liquidation->id) }}" class="text-indigo-600 hover:text-indigo-900 underline">{{$liquidation->id}}</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="p-3 text-center text-gray-500">No liquidations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $liquidations->links() }}
    </div>
</div>
@endsection
