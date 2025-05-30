<!-- resources/views/companies/table.blade.php -->
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">Supplier Code</th>
            <th class="py-2 px-4 border-b">Supplier Name</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($suppliers as $supplier)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $supplier->supplier_code }}</td>
                <td class="py-2 px-4 border-b">{{ $supplier->supplier_name }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" style="display:inline;">
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
    {{ $suppliers->links('pagination::tailwind') }}
</div>
