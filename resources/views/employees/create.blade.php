@extends('layouts.app')

@section('title', 'Create Employee')

@section('content')
<div class="min-h-screen bg-[linear-gradient(180deg,#f8fafc_0%,#eef4ff_100%)] py-8">
    <div class="mx-auto max-w-6xl px-4">
        <div class="mb-6 rounded-[28px] border border-white/70 bg-white/80 px-6 py-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                        <i class="fas fa-user-plus"></i>
                        New Employee
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Create Employee</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Add a complete employee profile, assign compensation details, and choose the right module access in one clean form.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                        <i class="fas fa-arrow-left text-xs"></i>
                        Back to Employees
                    </a>
                </div>
            </div>
        </div>

        <div class="rounded-[30px] border border-white/70 bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] sm:p-8">
            @if($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold">Please review the employee details below.</p>
                            <ul class="mt-2 list-disc pl-5 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('employees.store') }}" method="POST" class="space-y-8" novalidate>
                @csrf

                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Basic Details</h2>
                        <p class="mt-1 text-sm text-slate-500">Capture the employee identity and employment setup first.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="employee_code" class="mb-2 block text-sm font-semibold text-slate-700">Employee Code</label>
                            <input type="text" name="employee_code" id="employee_code" value="{{ old('employee_code') }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="first_name" class="mb-2 block text-sm font-semibold text-slate-700">First Name</label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="last_name" class="mb-2 block text-sm font-semibold text-slate-700">Last Name</label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="position" class="mb-2 block text-sm font-semibold text-slate-700">Position</label>
                            <input type="text" name="position" id="position" value="{{ old('position') }}" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                            <select name="status" id="status" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="employment_status" class="mb-2 block text-sm font-semibold text-slate-700">Employment Status</label>
                            <select name="employment_status" id="employment_status" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="regular" {{ old('employment_status') == 'regular' ? 'selected' : '' }}>Regular</option>
                                <option value="probationary" {{ old('employment_status') == 'probationary' ? 'selected' : '' }}>Probationary</option>
                                <option value="terminated" {{ old('employment_status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                <option value="suspended" {{ old('employment_status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Compensation Details</h2>
                        <p class="mt-1 text-sm text-slate-500">Add the employee compensation setup and government account references.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="daily_rate" class="mb-2 block text-sm font-semibold text-slate-700">Daily Rate</label>
                            <input type="number" step="0.01" min="0" name="daily_rate" id="daily_rate" value="{{ old('daily_rate') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="monthly_salary" class="mb-2 block text-sm font-semibold text-slate-700">Fixed Monthly Salary</label>
                            <input type="number" step="0.01" min="0" name="monthly_salary" id="monthly_salary" value="{{ old('monthly_salary') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="sss_no" class="mb-2 block text-sm font-semibold text-slate-700">SSS Number</label>
                            <input type="text" name="sss_no" id="sss_no" value="{{ old('sss_no') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="philhealth_no" class="mb-2 block text-sm font-semibold text-slate-700">PhilHealth Number</label>
                            <input type="text" name="philhealth_no" id="philhealth_no" value="{{ old('philhealth_no') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="tin_no" class="mb-2 block text-sm font-semibold text-slate-700">TIN</label>
                            <input type="text" name="tin_no" id="tin_no" value="{{ old('tin_no') }}" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Account Security</h2>
                        <p class="mt-1 text-sm text-slate-500">Set the initial password for the employee account.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                            <input type="password" name="password" id="password" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                            <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Access Roles</h2>
                        <p class="mt-1 text-sm text-slate-500">Choose the modules this employee can access. Parent roles will stay synced automatically.</p>
                    </div>
                    <div class="rounded-[28px] border border-slate-200 bg-slate-50/70 p-5 sm:p-6">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            @foreach ([
                                ['Allocation', [8, 9, 30]],
                                ['Delivery Request', [6, 7, 29]],
                                ['Coordinator', [10, 11, 31]],
                                ['Cash Voucher', [12, 13, 14, 15, 16, 17, 18, 32]],
                                ['Liquidation', [20, 21, 22, 23, 24, 25, 26, 27, 33]],
                                ['Settings', [3, 4, 5, 33, 34, 35, 28]],
                                ['Dashboard', [37, 38, 39, 40, 41, 42]],
                                ['Running Balance', [35, 43, 44, 45]],
                            ] as [$groupLabel, $groupIds])
                                <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                    <p class="mb-3 text-sm font-semibold text-slate-800">{{ $groupLabel }}</p>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        @foreach($roles as $role)
                                            @if(in_array($role->id, $groupIds))
                                                <label class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="role-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" {{ collect(old('roles'))->contains($role->id) ? 'checked' : '' }}>
                                                    <span>{{ $role->name }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-4 text-sm text-slate-500">Select one or more roles for the employee.</p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Double-check role access before saving so the employee only sees the modules they need.</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:bg-slate-50">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                        <button type="submit" style="background-color:#1d4ed8;border-color:#1d4ed8;color:#ffffff;" class="inline-flex min-w-[220px] appearance-none items-center justify-center gap-2 whitespace-nowrap rounded-2xl border px-6 py-3 text-sm font-semibold shadow-none outline-none ring-0 transition hover:opacity-95 focus:outline-none focus:ring-0 focus-visible:outline-none">
                            <i class="fas fa-save"></i>
                            Create Employee
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleGroups = [
            { main: '30', triggers: ['8', '9'] },
            { main: '29', triggers: ['6', '7'] },
            { main: '31', triggers: ['10', '11'] },
            { main: '32', triggers: ['12','13','14','15','16','17','18'] },
            { main: '33', triggers: ['20','21','22','23','24','25','26','27'] },
            { main: '28', triggers: ['3','4','5', '33', '34'] },
            { main: '35', triggers: ['43','44','45'] },
        ];

        const dashboardMasterId = '42';
        const dashboardChildIds = ['37','38','39','40','41'];
        const allCheckboxes = document.querySelectorAll('.role-checkbox');

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

                if (dashboardChildIds.includes(this.value)) {
                    dashboardChildIds.forEach(id => {
                        if (id !== this.value) {
                            const cb = document.querySelector(`input[type="checkbox"][value="${id}"]`);
                            if (cb) cb.checked = false;
                        }
                    });

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
