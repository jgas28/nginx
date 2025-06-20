@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-white p-10 rounded-2xl shadow-md mt-10">
    <h2 class="text-3xl font-semibold mb-8 text-gray-800">Update Employee</h2>
    <form action="{{ route('employees.update', $employee) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf
        @method('PUT')

        <div>
            <label for="employee_code" class="block text-sm font-medium text-gray-700">Employee Code</label>
            <input type="text" name="employee_code" id="employee_code" required value="{{ $employee->employee_code }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
            <input type="text" name="first_name" id="first_name" required value="{{ $employee->fname }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
            <input type="text" name="last_name" id="last_name" required value="{{ $employee->lname }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
            <input type="text" name="position" id="position" required value="{{ $employee->position }}" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

         <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password (Optional)</label>
            <input type="password" name="password" id="password" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
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
                                        class="form-checkbox h-5 w-5 text-indigo-600 rounded role-checkbox"
                                        {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                                        class="form-checkbox h-5 w-5 text-indigo-600 rounded role-checkbox"
                                        {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                                        class="form-checkbox h-5 w-5 text-indigo-600 rounded role-checkbox"
                                        {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                                        class="form-checkbox h-5 w-5 text-indigo-600 rounded role-checkbox"
                                        {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                                        class="form-checkbox h-5 w-5 text-indigo-600 rounded role-checkbox"
                                        {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Settings Group --}}
                <div>
                    <p class="font-semibold text-gray-800 mb-2">Settings:</p>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($roles as $role)
                            @if(in_array($role->id, [3, 4, 5, 28, 33, 34, 35]))
                                <label class="inline-flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="roles[]" 
                                        value="{{ $role->id }}" 
                                        class="form-checkbox h-5 w-5 text-indigo-600 rounded role-checkbox"
                                        {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Dashboard Group --}}
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
                                        class="form-checkbox h-5 w-5 text-indigo-600 rounded role-checkbox"
                                        {{ in_array($role->id, old('roles', $employee->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <span class="text-gray-700">{{ $role->name }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>
                </div>

            </div>

            <p class="text-sm text-gray-500 mt-1">Select one or more roles for the employee.</p>
        </div>
        
        <div class="md:col-span-2 pt-4">
            <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-indigo-700 transition duration-300">
                Update Employee
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

        // Initial check for pre-filled form
        function applyInitialChecks() {
            roleGroups.forEach(group => {
                const mainCheckbox = document.querySelector(`input[type="checkbox"][value="${group.main}"]`);
                if (!mainCheckbox) return;
                const isAnyTriggerChecked = group.triggers.some(id => {
                    const cb = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                    return cb && cb.checked;
                });
                mainCheckbox.checked = isAnyTriggerChecked;
            });

            const dashboardMaster = document.querySelector(`input[type="checkbox"][value="${dashboardMasterId}"]`);
            if (dashboardMaster) {
                const isAnyDashboardChecked = dashboardChildIds.some(id => {
                    const cb = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                    return cb && cb.checked;
                });
                dashboardMaster.checked = isAnyDashboardChecked;
            }
        }

        applyInitialChecks();

        // Checkbox change handler
        allCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                roleGroups.forEach(group => {
                    const mainCheckbox = document.querySelector(`input[type="checkbox"][value="${group.main}"]`);
                    if (!mainCheckbox) return;

                    const isAnyTriggerChecked = group.triggers.some(id => {
                        const cb = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                        return cb && cb.checked;
                    });

                    mainCheckbox.checked = isAnyTriggerChecked;
                });

                // Dashboard behavior
                if (dashboardChildIds.includes(this.value)) {
                    // Uncheck others
                    dashboardChildIds.forEach(id => {
                        if (id !== this.value) {
                            const cb = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                            if (cb) cb.checked = false;
                        }
                    });

                    // Update dashboard master
                    const dashboardMaster = document.querySelector(`input[type="checkbox"][value="${dashboardMasterId}"]`);
                    const anyDashboardChecked = dashboardChildIds.some(id => {
                        const cb = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                        return cb && cb.checked;
                    });
                    if (dashboardMaster) {
                        dashboardMaster.checked = anyDashboardChecked;
                    }
                }
            });
        });
    });
</script>

@endsection
