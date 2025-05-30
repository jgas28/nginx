<!-- resources/views/employees/table.blade.php -->
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">Name</th>
            <th class="py-2 px-4 border-b">Site</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($approvers as $approver)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $approver->name }}</td>
                <td class="py-2 px-4 border-b">{{ $approver->site }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('approvers.edit', $approver) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                    <form action="{{ route('approvers.destroy', $approver) }}" method="POST" style="display:inline;">
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
    {{ $approvers->links('pagination::tailwind') }}
</div>
