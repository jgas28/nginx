@extends('layouts.app')

@section('title', 'Manage Permissions')

@section('content')
<div class="min-h-screen bg-[linear-gradient(180deg,#f8fafc_0%,#eef4ff_100%)] py-8">
    <div class="mx-auto max-w-6xl px-4">
        <div class="mb-6 rounded-[28px] border border-white/70 bg-white/80 px-6 py-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] sm:px-8">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                        <i class="fas fa-user-shield"></i>
                        Employee Permissions
                    </div>
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ trim($employee->fname . ' ' . $employee->lname) }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Toggle "Override" on a module to set this employee's View/Create/Edit/Delete directly, independent of their roles. Leave it off to keep inheriting from their role(s) — shown as the muted "Role grants" hint.</p>
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
                            <p class="text-sm font-semibold">Please review the permissions below.</p>
                            <ul class="mt-2 list-disc pl-5 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('employees.permissions.update', $employee) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="global-table-scroll rounded-[28px] border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200" id="permissions-matrix">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Module</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Override</th>
                                @foreach(['view' => 'View', 'create' => 'Create', 'edit' => 'Edit', 'delete' => 'Delete'] as $action => $label)
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($modules as $module)
                                @php($existing = $overridesByModule->get($module->id))
                                @php($baseline = $roleBaselineByModule[$module->id] ?? ['can_view' => false, 'can_create' => false, 'can_edit' => false, 'can_delete' => false])
                                <tr class="module-row" data-module-row>
                                    <td class="px-6 py-3 text-sm font-medium text-slate-900">
                                        {{ $module->name }}
                                        <p class="mt-0.5 text-xs font-normal text-slate-400">
                                            Role grants:
                                            V {{ $baseline['can_view'] ? '✓' : '✕' }} ·
                                            C {{ $baseline['can_create'] ? '✓' : '✕' }} ·
                                            E {{ $baseline['can_edit'] ? '✓' : '✕' }} ·
                                            D {{ $baseline['can_delete'] ? '✓' : '✕' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <input
                                            type="checkbox"
                                            class="override-toggle h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                            name="permissions[{{ $module->id }}][override]"
                                            value="1"
                                            {{ $existing ? 'checked' : '' }}
                                        >
                                    </td>
                                    @foreach(['view', 'create', 'edit', 'delete'] as $action)
                                        <td class="px-6 py-3 text-center">
                                            <input
                                                type="checkbox"
                                                class="permission-checkbox h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                name="permissions[{{ $module->id }}][can_{{ $action }}]"
                                                value="1"
                                                {{ !$existing ? 'disabled' : '' }}
                                                {{ $existing && $existing->{"can_{$action}"} ? 'checked' : '' }}
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">Modules without "Override" checked keep inheriting from this employee's role(s).</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button type="submit" style="background-color:#1d4ed8;border-color:#1d4ed8;color:#ffffff;" class="inline-flex min-w-[220px] appearance-none items-center justify-center gap-2 whitespace-nowrap rounded-2xl border px-6 py-3 text-sm font-semibold shadow-none outline-none ring-0 transition hover:opacity-95 focus:outline-none focus:ring-0 focus-visible:outline-none">
                            <i class="fas fa-save"></i>
                            Save Permissions
                        </button>
                    </div>
                </div>
            </form>

            <form action="{{ route('employees.permissions.reset', $employee) }}" method="POST" class="mt-4" data-confirm-message="This clears every override for this employee and reverts them fully to role-based permissions. Continue?" data-confirm-title="Reset to Role Defaults?">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:-translate-y-0.5 hover:bg-rose-100">
                    <i class="fas fa-rotate-left"></i>
                    Reset to Role Defaults
                </button>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#permissions-matrix .module-row').forEach(function (row) {
            var toggle = row.querySelector('.override-toggle');
            var checkboxes = row.querySelectorAll('.permission-checkbox');

            function sync() {
                checkboxes.forEach(function (checkbox) {
                    checkbox.disabled = !toggle.checked;
                });
            }

            toggle.addEventListener('change', sync);
            sync();
        });
    });
</script>
@endsection
