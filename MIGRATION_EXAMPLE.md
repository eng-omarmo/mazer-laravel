# Migration Example: Updating ExpenseController

This file shows how to migrate from the old role-based system to Spatie Permission.

## Before (Old System)

```php
public function approve(Expense $expense)
{
    $role = strtolower(auth()->user()->role ?? 'hrm');
    if (! in_array($role, ['admin'])) {
        abort(403, 'Only Admin can approve expenses.');
    }

    $expense->update(['status' => 'approved']);
    return back()->with('status', 'Expense approved');
}

public function pay(Request $request, Expense $expense)
{
    $role = strtolower(auth()->user()->role ?? 'hrm');
    if (! in_array($role, ['finance', 'admin'])) {
        abort(403, 'Only Finance can initiate payments.');
    }
    // ... rest of code
}
```

## After (Spatie Permission System)

### Option 1: Check in Controller

```php
public function approve(Expense $expense)
{
    if (!auth()->user()->hasPermissionTo('approve expenses')) {
        abort(403, 'You do not have permission to approve expenses.');
    }

    $expense->update(['status' => 'approved']);
    return back()->with('status', 'Expense approved');
}

public function pay(Request $request, Expense $expense)
{
    if (!auth()->user()->hasAnyPermission(['pay expenses', 'approve expenses'])) {
        abort(403, 'You do not have permission to initiate payments.');
    }
    // ... rest of code
}
```

### Option 2: Use Middleware in Routes (Recommended)

**In `routes/web.php`:**
```php
Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])
    ->middleware(['auth', 'permission:approve expenses'])
    ->name('expenses.approve');

Route::post('/expenses/{expense}/pay', [ExpenseController::class, 'pay'])
    ->middleware(['auth', 'permission:pay expenses'])
    ->name('expenses.pay');
```

**In Controller (simplified):**
```php
public function approve(Expense $expense)
{
    // No need to check permission - middleware handles it
    $expense->update(['status' => 'approved']);
    return back()->with('status', 'Expense approved');
}

public function pay(Request $request, Expense $expense)
{
    // No need to check permission - middleware handles it
    // ... rest of code
}
```

## Benefits of Using Middleware

1. **Cleaner Controllers** - No permission checks cluttering business logic
2. **Consistent Protection** - All routes protected the same way
3. **Easier Testing** - Test middleware separately
4. **Better Organization** - Permission logic in one place

## Complete Route Example

```php
Route::prefix('hrm')->name('hrm.')->middleware('auth')->group(function () {
    
    // Expenses - with permission checks
    Route::get('/expenses', [ExpenseController::class, 'index'])
        ->middleware('permission:view expenses')
        ->name('expenses.index');
    
    Route::get('/expenses/create', [ExpenseController::class, 'create'])
        ->middleware('permission:create expenses')
        ->name('expenses.create');
    
    Route::post('/expenses', [ExpenseController::class, 'store'])
        ->middleware('permission:create expenses')
        ->name('expenses.store');
    
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])
        ->middleware('permission:view expenses')
        ->name('expenses.show');
    
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])
        ->middleware('permission:edit expenses')
        ->name('expenses.edit');
    
    Route::patch('/expenses/{expense}', [ExpenseController::class, 'update'])
        ->middleware('permission:edit expenses')
        ->name('expenses.update');
    
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])
        ->middleware('permission:delete expenses')
        ->name('expenses.destroy');
    
    Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])
        ->middleware('permission:approve expenses')
        ->name('expenses.approve');
    
    Route::post('/expenses/{expense}/pay', [ExpenseController::class, 'pay'])
        ->middleware('permission:pay expenses')
        ->name('expenses.pay');
});
```

## Using Role-Based Middleware (Alternative)

If you prefer to check roles instead of permissions:

```php
// Single role
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin only routes
});

// Multiple roles (OR logic)
Route::middleware(['auth', 'role:admin,finance'])->group(function () {
    // Admin OR Finance routes
});
```

## Blade Template Updates

### Before
```blade
@if(strtolower(auth()->user()->role ?? '') === 'admin')
    <button>Approve</button>
@endif
```

### After
```blade
@can('approve expenses')
    <button>Approve</button>
@endcan

{{-- Or using role --}}
@role('admin')
    <button>Approve</button>
@endrole
```
