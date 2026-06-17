@extends('layouts.app')

@section('title', 'Role Permissions')

@section('content')
<div class="min-h-screen bg-slate-50 py-6">
    <div class="mx-auto max-w-5xl px-4">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Role Permissions</h1>
            <p class="mt-1 text-sm text-slate-500">Choose a role to manage its View / Create / Edit / Delete access per module.</p>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Role</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($roles as $role)
                        <tr>
                            <td class="px-6 py-3 text-sm text-slate-500">{{ $role->id }}</td>
                            <td class="px-6 py-3 text-sm font-medium text-slate-900">{{ $role->name }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('roles.permissions.edit', $role) }}" class="global-edit-action inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold">
                                    <i class="fas fa-sliders"></i>
                                    Manage Permissions
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
