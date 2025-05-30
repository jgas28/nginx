<!-- resources/views/Warehouse/table.blade.php -->
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">Warehouse Code</th>
            <th class="py-2 px-4 border-b">Warehouse Name</th>
            <th class="py-2 px-4 border-b">Warehouse Location</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($warehouses as $warehouse)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $warehouse->warehouse_code }}</td>
                <td class="py-2 px-4 border-b">{{ $warehouse->warehouse_name }}</td>
                <td class="py-2 px-4 border-b">{{ $warehouse->warehouse_location }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                    <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" style="display:inline;">
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
    {{ $warehouses->links('pagination::tailwind') }}
</div>
