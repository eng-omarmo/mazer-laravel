<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('name')->paginate(15);
        return view('admin.permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:permissions,name', 'max:255'],
        ]);

        $permission = Permission::create(['name' => $validated['name'], 'guard_name' => 'web']);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created Permission',
            'meta' => ['permission' => $permission->name],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.permissions.index')->with('status', 'Permission created successfully');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:permissions,name,' . $permission->id, 'max:255'],
        ]);

        $oldName = $permission->name;
        $permission->update(['name' => $validated['name']]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated Permission',
            'meta' => [
                'permission_id' => $permission->id,
                'old_name' => $oldName,
                'new_name' => $permission->name
            ],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.permissions.index')->with('status', 'Permission updated successfully');
    }

    public function destroy(Request $request, Permission $permission)
    {
        $name = $permission->name;
        $permission->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted Permission',
            'meta' => ['permission' => $name],
            'ip' => $request->ip(),
        ]);

        return redirect()->route('admin.permissions.index')->with('status', 'Permission deleted successfully');
    }
}
