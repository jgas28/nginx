<!-- resources/views/add_on_rates/table.blade.php -->
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">Add-On Rate Type Code</th>
            <th class="py-2 px-4 border-b">Add-On Rate Type Name</th>
            <th class="py-2 px-4 border-b">Rate</th>
            <th class="py-2 px-4 border-b">Add Percentage Rate</th>
            <th class="py-2 px-4 border-b">Delivery Type</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($addOnRates as $addOnRate)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $addOnRate->add_on_rate_type_code }}</td>
                <td class="py-2 px-4 border-b">{{ $addOnRate->add_on_rate_type_name }}</td>
                <td class="py-2 px-4 border-b">{{ $addOnRate->rate }}</td>
                <td class="py-2 px-4 border-b">{{ $addOnRate->percent_rate }}</td>
                <td class="py-2 px-4 border-b">{{ $addOnRate->delivery_type }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('addOnRates.edit', $addOnRate) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                    <form action="{{ route('addOnRates.destroy', $addOnRate) }}" method="POST" style="display:inline;">
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
    {{ $addOnRates->links('pagination::tailwind') }}
</div>
