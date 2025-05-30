<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">Request Type Code</th>
            <th class="py-2 px-4 border-b">Request Type Name</th>
            <th class="py-2 px-4 border-b">Group</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cvr_request_types as $cvr_request_type)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $cvr_request_type->request_code }}</td>
                <td class="py-2 px-4 border-b">{{ $cvr_request_type->request_type }}</td>
                <td class="py-2 px-4 border-b">{{ $cvr_request_type->group_type }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('cvr_request_types.edit', $cvr_request_type) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                    <form action="{{ route('cvr_request_types.destroy', $cvr_request_type) }}" method="POST" style="display:inline;">
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

<div class="mt-4">
    {{ $cvr_request_types->links('pagination::tailwind') }}
</div>
