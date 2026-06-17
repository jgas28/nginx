<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\RoleModulePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    public function edit(User $employee)
    {
        $modules = Module::orderBy('name')->get();
        $overridesByModule = $employee->modulePermissions()->get()->keyBy('module_id');
        $roleBaselineByModule = $this->roleBaseline($employee);

        return view('employees.permissions', compact('employee', 'modules', 'overridesByModule', 'roleBaselineByModule'));
    }

    public function update(Request $request, User $employee)
    {
        $data = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*.override' => 'nullable|boolean',
            'permissions.*.can_view' => 'nullable|boolean',
            'permissions.*.can_create' => 'nullable|boolean',
            'permissions.*.can_edit' => 'nullable|boolean',
            'permissions.*.can_delete' => 'nullable|boolean',
        ]);

        $permissions = $data['permissions'] ?? [];

        foreach (Module::all() as $module) {
            $flags = $permissions[$module->id] ?? null;

            // No "override" toggle submitted for this module -- inherit from role, same as
            // today. Delete any existing override row rather than leave a stale one behind.
            if (empty($flags['override'])) {
                UserModulePermission::where('user_id', $employee->id)->where('module_id', $module->id)->delete();
                continue;
            }

            UserModulePermission::updateOrCreate(
                ['user_id' => $employee->id, 'module_id' => $module->id],
                [
                    'can_view' => (bool) ($flags['can_view'] ?? false),
                    'can_create' => (bool) ($flags['can_create'] ?? false),
                    'can_edit' => (bool) ($flags['can_edit'] ?? false),
                    'can_delete' => (bool) ($flags['can_delete'] ?? false),
                ]
            );
        }

        return redirect()->route('employees.permissions.edit', $employee)->with('success', 'Permissions updated successfully.');
    }

    public function reset(User $employee)
    {
        $employee->modulePermissions()->delete();

        return redirect()->route('employees.permissions.edit', $employee)->with('success', 'Reverted to role defaults.');
    }

    private function roleBaseline(User $employee): array
    {
        $roleIds = $employee->roles->pluck('id');

        return RoleModulePermission::whereIn('role_id', $roleIds)
            ->get()
            ->groupBy('module_id')
            ->map(fn ($rows) => [
                'can_view' => $rows->contains(fn ($row) => $row->can_view),
                'can_create' => $rows->contains(fn ($row) => $row->can_create),
                'can_edit' => $rows->contains(fn ($row) => $row->can_edit),
                'can_delete' => $rows->contains(fn ($row) => $row->can_delete),
            ])
            ->all();
    }
}
