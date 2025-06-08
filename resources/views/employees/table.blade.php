<!-- resources/views/employees/table.blade.php -->
<table class="table-auto w-full text-sm text-left text-gray-600">
    <thead class="bg-gray-100 text-gray-700">
        <tr>
            <th class="py-2 px-4 border-b">Employee Code</th>
            <th class="py-2 px-4 border-b">First Name</th>
            <th class="py-2 px-4 border-b">Last Name</th>
            <th class="py-2 px-4 border-b">Position</th>
            <th class="py-2 px-4 border-b">Role</th>
            <th class="py-2 px-4 border-b">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $employee->employee_code }}</td>
                <td class="py-2 px-4 border-b">{{ $employee->fname }}</td>
                <td class="py-2 px-4 border-b">{{ $employee->lname }}</td>
                <td class="py-2 px-4 border-b">{{ $employee->position }}</td>
                <td class="py-2 px-4 border-b">{{ $employee->role->name }}</td>
                <td class="py-2 px-4 border-b">
                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-warning bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                        Edit
                    </a>
                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" style="display:inline;">
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
    {{ $employees->links('pagination::tailwind') }}
</div>
