<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->paginate(10);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);

        if (! empty($validated['permissions'])) {
            $perms = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($perms);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created Role',
            'meta' => ['role' => $role->name, 'permissions' => $validated['permissions'] ?? []],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'Role created successfully');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name,'.$role->id, 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $oldName = $role->name;
        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $perms = Permission::whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($perms);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated Role',
            'meta' => [
                'role_id' => $role->id,
                'old_name' => $oldName,
                'new_name' => $role->name,
                'permissions' => $validated['permissions'] ?? [],
            ],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'Role updated successfully');
    }

    public function destroy(Request $request, Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'Cannot delete Admin role');
        }

        $roleName = $role->name;
        $role->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted Role',
            'meta' => ['role' => $roleName],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted successfully');
    }
}
