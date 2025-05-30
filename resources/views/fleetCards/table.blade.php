<!-- resources/views/employees/table.blade.php -->
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">Account</th>
            <th class="py-2 px-4 border-b">Account Name</th>
            <th class="py-2 px-4 border-b">Account Number</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($fleetCards as $fleetCard)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $fleetCard->account }}</td>
                <td class="py-2 px-4 border-b">{{ $fleetCard->account_name }}</td>
                <td class="py-2 px-4 border-b">{{ $fleetCard->account_number }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('fleetCards.edit', $fleetCard) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                    <form action="{{ route('fleetCards.destroy', $fleetCard) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Pagination -->
<div class="mt-4">
    {{ $fleetCards->links('pagination::tailwind') }}
</div>
