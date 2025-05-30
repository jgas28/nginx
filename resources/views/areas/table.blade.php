<!-- resources/views/employees/table.blade.php -->
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">Area Code</th>
            <th class="py-2 px-4 border-b">Area Name</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($regions as $region)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $region->area_code }}</td>
                <td class="py-2 px-4 border-b">{{ $region->area_name }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('areas.edit', $region) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                    <form action="{{ route('areas.destroy', $region) }}" method="POST" style="display:inline;">
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
    {{ $regions->links('pagination::tailwind') }}
</div>
