<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionApiController extends Controller
{
    public function permissions(Request $request)
    {
        $items = Permission::orderBy('name')->get(['id', 'name', 'guard_name']);

        return response()->json(['data' => $items]);
    }

    public function roles(Request $request)
    {
        $items = Role::orderBy('name')->get(['id', 'name', 'guard_name']);

        return response()->json(['data' => $items]);
    }

    public function syncRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $perms = Permission::whereIn('name', $validated['permissions'])->get();
        $role->syncPermissions($perms);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Sync Role Permissions',
            'meta' => [
                'role_id' => $role->id,
                'permissions' => $validated['permissions'],
            ],
            'ip' => $request->ip(),
        ]);

        return response()->json(['ok' => true]);
    }
}
