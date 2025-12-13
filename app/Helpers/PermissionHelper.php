<?php

namespace App\Helpers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionHelper
{
    /**
     * Check if user has a specific role
     */
    public static function hasRole(User $user, string|array $roles): bool
    {
        return $user->hasRole($roles);
    }

    /**
     * Check if user has a specific permission
     */
    public static function hasPermission(User $user, string|array $permissions): bool
    {
        return $user->hasPermissionTo($permissions);
    }

    /**
     * Check if user has any of the given roles
     */
    public static function hasAnyRole(User $user, array $roles): bool
    {
        return $user->hasAnyRole($roles);
    }

    /**
     * Check if user has any of the given permissions
     */
    public static function hasAnyPermission(User $user, array $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Assign role to user
     */
    public static function assignRole(User $user, string|Role $role): void
    {
        $user->assignRole($role);
    }

    /**
     * Remove role from user
     */
    public static function removeRole(User $user, string|Role $role): void
    {
        $user->removeRole($role);
    }

    /**
     * Give permission to user
     */
    public static function givePermission(User $user, string|Permission $permission): void
    {
        $user->givePermissionTo($permission);
    }

    /**
     * Revoke permission from user
     */
    public static function revokePermission(User $user, string|Permission $permission): void
    {
        $user->revokePermissionTo($permission);
    }

    /**
     * Get all roles
     */
    public static function getAllRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::all();
    }

    /**
     * Get all permissions
     */
    public static function getAllPermissions(): \Illuminate\Database\Eloquent\Collection
    {
        return Permission::all();
    }

    /**
     * Get user's roles
     */
    public static function getUserRoles(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->roles;
    }

    /**
     * Get user's permissions (direct + via roles)
     */
    public static function getUserPermissions(User $user): \Illuminate\Support\Collection
    {
        return $user->getAllPermissions();
    }
}
