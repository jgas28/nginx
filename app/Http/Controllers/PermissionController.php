<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Role;
use App\Models\RoleModulePermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id')->get();

        return view('roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $modules = Module::orderBy('name')->get();
        $permissionsByModule = $role->modulePermissions()->get()->keyBy('module_id');

        return view('roles.permissions', compact('role', 'modules', 'permissionsByModule'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*.can_view' => 'nullable|boolean',
            'permissions.*.can_create' => 'nullable|boolean',
            'permissions.*.can_edit' => 'nullable|boolean',
            'permissions.*.can_delete' => 'nullable|boolean',
        ]);

        $permissions = $data['permissions'] ?? [];

        foreach (Module::all() as $module) {
            $flags = $permissions[$module->id] ?? [];

            RoleModulePermission::updateOrCreate(
                ['role_id' => $role->id, 'module_id' => $module->id],
                [
                    'can_view' => (bool) ($flags['can_view'] ?? false),
                    'can_create' => (bool) ($flags['can_create'] ?? false),
                    'can_edit' => (bool) ($flags['can_edit'] ?? false),
                    'can_delete' => (bool) ($flags['can_delete'] ?? false),
                ]
            );
        }

        return redirect()->route('roles.permissions.edit', $role)->with('success', 'Permissions updated successfully.');
    }
}
