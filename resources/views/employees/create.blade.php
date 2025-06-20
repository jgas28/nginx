@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white p-12 rounded-2xl shadow-md mt-10">
    <h2 class="text-3xl font-semibold mb-10 text-gray-800">Create New Employee</h2>
    <form action="{{ route('employees.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @csrf

        <div>
            <label for="employee_code" class="block text-sm font-medium text-gray-700">Employee Code</label>
            <input type="text" name="employee_code" id="employee_code" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" name="first_name" id="first_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" name="last_name" id="last_name" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
            <input type="text" name="position" id="position" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
            <div class="max-h-96 overflow-y-auto border border-gray-300 rounded-xl p-4 bg-gray-50 space-y-6">

                {{-- Allocation Group --}}
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Allocation:</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            @if(in_array($role->id, [8, 9, 30]))
                                <label class="inline-flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="roles[]" 
                                        value="{{ $role->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded role-checkbox"
                                        {{ (collect(old('roles'))->contains($role->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Delivery Request Group --}}
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Delivery Request:</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            @if(in_array($role->id, [6, 7, 29]))
                                <label class="inline-flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="roles[]" 
                                        value="{{ $role->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded role-checkbox"
                                        {{ (collect(old('roles'))->contains($role->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Coordinator Group --}}
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Coordinator:</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            @if(in_array($role->id, [10, 11, 31]))
                                <label class="inline-flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="roles[]" 
                                        value="{{ $role->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded role-checkbox"
                                        {{ (collect(old('roles'))->contains($role->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Cash Voucher Group --}}
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Cash Voucher:</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            @if(in_array($role->id, [12, 13, 14, 15, 16, 17, 18, 32]))
                                <label class="inline-flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="roles[]" 
                                        value="{{ $role->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded role-checkbox"
                                        {{ (collect(old('roles'))->contains($role->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Liquidation Group --}}
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Liquidation:</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            @if(in_array($role->id, [20, 21, 22, 23, 24, 25, 26, 27, 33]))
                                <label class="inline-flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="roles[]" 
                                        value="{{ $role->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded role-checkbox"
                                        {{ (collect(old('roles'))->contains($role->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Setting Group --}}
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Settings:</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            @if(in_array($role->id, [3, 4, 5, 33, 34, 35, 28]))
                                <label class="inline-flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="roles[]" 
                                        value="{{ $role->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded role-checkbox"
                                        {{ (collect(old('roles'))->contains($role->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Dashboard --}}
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Dashboard:</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            @if(in_array($role->id, [37, 38, 39, 40, 41, 42]))
                                <label class="inline-flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="roles[]" 
                                        value="{{ $role->id }}" 
                                        class="form-checkbox h-5 w-5 text-blue-600 rounded role-checkbox"
                                        {{ (collect(old('roles'))->contains($role->id)) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

            </div>
            <p class="text-sm text-gray-500 mt-1">Select one or more roles for the employee.</p>
        </div>

        <div class="md:col-span-2 pt-6">
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-blue-700 transition duration-300">
                Create Employee
            </button>
        </div>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleGroups = [
            { main: '30', triggers: ['8', '9'] },    // Allocation
            { main: '29', triggers: ['6', '7'] },    // Delivery Request
            { main: '31', triggers: ['10', '11'] },  // Coordinator
            { main: '32', triggers: ['12','13','14','15','16','17','18'] }, // Cash Voucher
            { main: '33', triggers: ['20','21','22','23','24','25','26','27'] }, // Liquidation
            { main: '28', triggers: ['3','4','5', '33', '34', '35'] }, // Settings
        ];

        const dashboardMasterId = '42';
        const dashboardChildIds = ['37','38','39','40','41'];

        const allCheckboxes = document.querySelectorAll('.role-checkbox');

        // Standard group logic
        allCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                roleGroups.forEach(group => {
                    const mainCheckbox = document.querySelector(`input[type="checkbox"][value="${group.main}"]`);
                    if (!mainCheckbox) return;

                    const isAnyTriggerChecked = group.triggers.some(id => {
                        const triggerCheckbox = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                        return triggerCheckbox && triggerCheckbox.checked;
                    });

                    mainCheckbox.checked = isAnyTriggerChecked;
                });

                // Dashboard logic
                if (dashboardChildIds.includes(this.value)) {
                    // Uncheck all others in the dashboard group
                    dashboardChildIds.forEach(id => {
                        if (id !== this.value) {
                            const cb = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                            if (cb) cb.checked = false;
                        }
                    });

                    // Check dashboard master if any dashboard role is selected
                    const anyChecked = dashboardChildIds.some(id => {
                        const cb = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                        return cb && cb.checked;
                    });

                    const dashboardMaster = document.querySelector(`input[type="checkbox"][value="${dashboardMasterId}"]`);
                    if (dashboardMaster) {
                        dashboardMaster.checked = anyChecked;
                    }
                }
            });
        });
    });
</script>
@endsection
