# Spatie Permission - Quick Reference

## 🚀 Setup Commands

```bash
# Run migrations (creates permission tables)
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolePermissionSeeder

# Clear permission cache
php artisan permission:cache-reset
```

## ✅ User Model Methods

```php
$user = auth()->user();

// Check role
$user->hasRole('admin')
$user->hasAnyRole(['admin', 'hrm'])
$user->hasAllRoles(['admin', 'hrm'])

// Check permission
$user->hasPermissionTo('create employees')
$user->hasAnyPermission(['create employees', 'edit employees'])
$user->hasAllPermissions(['create', 'edit'])

// Assign role
$user->assignRole('admin')
$user->assignRole(['admin', 'hrm'])

// Remove role
$user->removeRole('admin')

// Sync roles (replaces all)
$user->syncRoles(['hrm'])

// Give permission
$user->givePermissionTo('create employees')

// Revoke permission
$user->revokePermissionTo('create employees')

// Get all permissions (direct + via roles)
$user->getAllPermissions()

// Get roles
$user->roles
```

## 🛡️ Middleware Usage

### In Routes
```php
// Single role
Route::middleware(['auth', 'role:admin'])->group(...)

// Multiple roles (OR)
Route::middleware(['auth', 'role:admin,hrm'])->group(...)

// Single permission
Route::middleware(['auth', 'permission:create employees'])->group(...)

// Multiple permissions (OR)
Route::middleware(['auth', 'permission:create employees|edit employees'])->group(...)
```

### In Controllers
```php
// Check in constructor
public function __construct()
{
    $this->middleware('permission:view employees');
}

// Check in method
if (!auth()->user()->hasPermissionTo('create employees')) {
    abort(403);
}
```

## 🎨 Blade Directives

```blade
{{-- Check role --}}
@role('admin')
    Admin content
@endrole

{{-- Check multiple roles (OR) --}}
@hasanyrole('admin|hrm')
    Management content
@endhasanyrole

{{-- Check all roles (AND) --}}
@hasallroles('admin|hrm')
    Both roles required
@endhasallroles

{{-- Check permission --}}
@can('create employees')
    Create button
@endcan

{{-- Check any permission (OR) --}}
@canany(['create employees', 'edit employees'])
    Manage button
@endcanany

{{-- Check all permissions (AND) --}}
@canall(['create employees', 'edit employees'])
    Full access
@endcanall
```

## 📋 Available Roles

- `admin` - Full access
- `hrm` - HR Management
- `finance` - Financial operations
- `employee` - Basic access
- `credit_manager` - Credit management

## 🔐 Common Permissions

### Employees
- `view employees`
- `create employees`
- `edit employees`
- `delete employees`

### Expenses
- `view expenses`
- `create expenses`
- `edit expenses`
- `approve expenses`
- `pay expenses`

### Payroll
- `view payroll`
- `create payroll`
- `approve payroll`
- `mark payroll paid`

## 💡 Tips

1. **Use permissions, not roles** in code
2. **Use middleware** for route protection
3. **Clear cache** after permission changes
4. **Test thoroughly** after migration

## 🔄 Migration Pattern

```php
// OLD
if (!in_array($user->role, ['admin'])) {
    abort(403);
}

// NEW
if (!$user->hasPermissionTo('approve expenses')) {
    abort(403);
}

// OR use middleware
Route::middleware(['auth', 'permission:approve expenses'])
```

## 📚 Full Documentation

See `SPATIE_PERMISSION_SETUP.md` for complete documentation.
