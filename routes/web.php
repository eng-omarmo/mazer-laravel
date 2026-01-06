<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HrmReportsController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollBatchController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FingerprintController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $role = strtolower($user->role ?? 'hrm');
    if (in_array($role, ['hrm', 'admin'])) {
        if (\App\Models\PayrollBatch::where('status', 'submitted')->exists()) {
            session()->flash('status', 'Payroll approval waiting');
        }
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('hrm')->name('hrm.')->group(function () {
        Route::middleware(['can:view employees'])->group(function () {
            Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
            Route::get('/employees/create', [EmployeeController::class, 'create'])->middleware('can:create employees')->name('employees.create');
            Route::post('/employees', [EmployeeController::class, 'store'])->middleware('can:create employees')->name('employees.store');
            Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
            Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->middleware('can:edit employees')->name('employees.edit');
            Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->middleware('can:edit employees')->name('employees.update');
        });

        Route::middleware(['can:edit employees'])->group(function () {
            Route::get('/fingerprint', [FingerprintController::class, 'show'])->name('fingerprint.show');
            Route::post('/fingerprint/capture', [FingerprintController::class, 'capture'])->name('fingerprint.capture');
        });

        Route::middleware(['can:view documents'])->group(function () {
            Route::get('/verification', [DocumentVerificationController::class, 'index'])->name('verification.index');
            Route::post('/verification/{document}/approve', [DocumentVerificationController::class, 'approve'])->middleware('can:approve documents')->name('verification.approve');
            Route::post('/verification/{document}/reject', [DocumentVerificationController::class, 'reject'])->middleware('can:reject documents')->name('verification.reject');
        });

        Route::middleware(['can:view departments'])->group(function () {
            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::get('/departments/create', [DepartmentController::class, 'create'])->middleware('can:create departments')->name('departments.create');
            Route::post('/departments', [DepartmentController::class, 'store'])->middleware('can:create departments')->name('departments.store');
            Route::get('/departments/{department}', [DepartmentController::class, 'show'])->name('departments.show');
            Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->middleware('can:edit departments')->name('departments.edit');
            Route::patch('/departments/{department}', [DepartmentController::class, 'update'])->middleware('can:edit departments')->name('departments.update');
            Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->middleware('can:delete departments')->name('departments.destroy');
        });

        Route::middleware(['can:view leaves'])->group(function () {
            Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
            Route::get('/leave/create', [LeaveController::class, 'create'])->middleware('can:create leaves')->name('leave.create');
            Route::post('/leave', [LeaveController::class, 'store'])->middleware('can:create leaves')->name('leave.store');
            Route::get('/leave/{leave}/edit', [LeaveController::class, 'edit'])->middleware('can:edit leaves')->name('leave.edit');
            Route::patch('/leave/{leave}', [LeaveController::class, 'update'])->middleware('can:edit leaves')->name('leave.update');
            Route::delete('/leave/{leave}', [LeaveController::class, 'destroy'])->middleware('can:delete leaves')->name('leave.destroy');
            Route::post('/leave/{leave}/approve', [LeaveController::class, 'approve'])->middleware('can:approve leaves')->name('leave.approve');
            Route::post('/leave/{leave}/reject', [LeaveController::class, 'reject'])->middleware('can:approve leaves')->name('leave.reject');
        });

        Route::middleware(['can:view payroll'])->group(function () {
            Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
            Route::get('/payroll/create', [PayrollController::class, 'create'])->middleware('can:create payroll')->name('payroll.create');
            Route::post('/payroll', [PayrollController::class, 'store'])->middleware('can:create payroll')->name('payroll.store');
            Route::get('/payroll/{payroll}/edit', [PayrollController::class, 'edit'])->middleware('can:edit payroll')->name('payroll.edit');
            Route::patch('/payroll/{payroll}', [PayrollController::class, 'update'])->middleware('can:edit payroll')->name('payroll.update');
            Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->middleware('can:delete payroll')->name('payroll.destroy');
            Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->middleware('can:approve payroll')->name('payroll.approve');
            Route::post('/payroll/{payroll}/paid', [PayrollController::class, 'markPaid'])->middleware('can:mark payroll paid')->name('payroll.paid');
        });

        Route::middleware(['can:view attendance'])->group(function () {
            Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('/attendance/create', [AttendanceController::class, 'create'])->middleware('can:create attendance')->name('attendance.create');
            Route::post('/attendance', [AttendanceController::class, 'store'])->middleware('can:create attendance')->name('attendance.store');
            Route::get('/attendance/{log}/edit', [AttendanceController::class, 'edit'])->middleware('can:edit attendance')->name('attendance.edit');
            Route::patch('/attendance/{log}', [AttendanceController::class, 'update'])->middleware('can:edit attendance')->name('attendance.update');
            Route::get('/attendance/summary', [AttendanceController::class, 'summary'])->middleware('can:view attendance summary')->name('attendance.summary');
            Route::get('/attendance/export/csv', [AttendanceController::class, 'exportCsv'])->middleware('can:view attendance summary')->name('attendance.export.csv');
            Route::get('/attendance/my', [AttendanceController::class, 'myHistory'])->name('attendance.my');
        });

        Route::middleware(['can:view advances'])->group(function () {
            Route::get('/advances', [\App\Http\Controllers\EmployeeAdvanceController::class, 'index'])->name('advances.index');
            Route::get('/advances/create', [\App\Http\Controllers\EmployeeAdvanceController::class, 'create'])->middleware('can:create advances')->name('advances.create');
            Route::post('/advances', [\App\Http\Controllers\EmployeeAdvanceController::class, 'store'])->middleware('can:create advances')->name('advances.store');
            Route::post('/advances/{advance}/approve', [\App\Http\Controllers\EmployeeAdvanceController::class, 'approve'])->middleware('can:approve advances')->name('advances.approve');
            Route::post('/advances/{advance}/paid', [\App\Http\Controllers\EmployeeAdvanceController::class, 'markPaid'])->middleware('can:mark advance paid')->name('advances.paid');
            Route::get('/advances/{advance}', [\App\Http\Controllers\EmployeeAdvanceController::class, 'show'])->name('advances.show');
            Route::get('/advances/{advance}/repay', [\App\Http\Controllers\EmployeeAdvanceController::class, 'repay'])->middleware('can:approve advances')->name('advances.repay');
            Route::get('/advances/receipt/{transaction}', [\App\Http\Controllers\EmployeeAdvanceController::class, 'receipt'])->middleware('can:view advance receipts')->name('advances.receipt');
        });

        Route::middleware(['can:view suppliers'])->group(function () {
            Route::get('/suppliers', [\App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers.index');
            Route::get('/suppliers/create', [\App\Http\Controllers\SupplierController::class, 'create'])->middleware('can:create suppliers')->name('suppliers.create');
            Route::post('/suppliers', [\App\Http\Controllers\SupplierController::class, 'store'])->middleware('can:create suppliers')->name('suppliers.store');
            Route::get('/suppliers/{supplier}/edit', [\App\Http\Controllers\SupplierController::class, 'edit'])->middleware('can:edit suppliers')->name('suppliers.edit');
            Route::patch('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'update'])->middleware('can:edit suppliers')->name('suppliers.update');
            Route::delete('/suppliers/{supplier}', [\App\Http\Controllers\SupplierController::class, 'destroy'])->middleware('can:delete suppliers')->name('suppliers.destroy');
        });

        Route::middleware(['can:view expenses'])->group(function () {
            Route::get('/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
            Route::get('/expenses/create', [\App\Http\Controllers\ExpenseController::class, 'create'])->middleware('can:create expenses')->name('expenses.create');
            Route::post('/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->middleware('can:create expenses')->name('expenses.store');
            Route::get('/expenses/{expense}/edit', [\App\Http\Controllers\ExpenseController::class, 'edit'])->middleware('can:edit expenses')->name('expenses.edit');
            Route::get('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'show'])->name('expenses.show');
            Route::patch('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'update'])->middleware('can:edit expenses')->name('expenses.update');
            Route::delete('/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->middleware('can:delete expenses')->name('expenses.destroy');
            Route::post('/expenses/{expense}/review', [\App\Http\Controllers\ExpenseController::class, 'review'])->middleware('can:approve expenses')->name('expenses.review');
            Route::post('/expenses/{expense}/approve', [\App\Http\Controllers\ExpenseController::class, 'approve'])->middleware('can:approve expenses')->name('expenses.approve');
            Route::post('/expenses/{expense}/pay', [\App\Http\Controllers\ExpenseController::class, 'pay'])->middleware('can:pay expenses')->name('expenses.pay');
        });

        Route::get('/expenses/payments/pending', [\App\Http\Controllers\ExpenseController::class, 'pendingExpensePayments'])->middleware('can:view pending payments')->name('expenses.payments.pending');

        Route::post('/expenses/payments/{payment}/approve', [\App\Http\Controllers\ExpenseController::class, 'approvePayment'])->middleware('can:pay expenses')->name('expense-payments.approve');
        Route::post('/expenses/payments/{payment}/reject', [\App\Http\Controllers\ExpenseController::class, 'rejectPayment'])->middleware('can:pay expenses')->name('expense-payments.reject');

        Route::get('/payroll/batches', [PayrollBatchController::class, 'index'])->middleware('can:view payroll batches')->name('payroll.batches.index');
        Route::get('/payroll/batches/create', [PayrollBatchController::class, 'create'])->middleware('can:create payroll batches')->name('payroll.batches.create');
        Route::post('/payroll/batches', [PayrollBatchController::class, 'store'])->middleware('can:create payroll batches')->name('payroll.batches.store');
        Route::get('/payroll/batches/{batch}', [PayrollBatchController::class, 'show'])->middleware('can:view payroll batches')->name('payroll.batches.show');
        Route::patch('/payroll/batches/{batch}', [PayrollBatchController::class, 'update'])->middleware('can:edit payroll batches')->name('payroll.batches.update');
        Route::post('/payroll/batches/{batch}/submit', [PayrollBatchController::class, 'submit'])->middleware('can:submit payroll batches')->name('payroll.batches.submit');
        Route::post('/payroll/batches/{batch}/approve', [PayrollBatchController::class, 'approve'])->middleware('can:approve payroll batches')->name('payroll.batches.approve');
        Route::post('/payroll/batches/{batch}/reject', [PayrollBatchController::class, 'reject'])->middleware('can:reject payroll batches')->name('payroll.batches.reject');
        Route::post('/payroll/batches/{batch}/paid', [PayrollBatchController::class, 'markPaid'])->middleware('can:mark batch paid')->name('payroll.batches.paid');
        Route::post('/payroll/batches/approve-all', [PayrollBatchController::class, 'approveAllPending'])->middleware('can:approve payroll batches')->name('payroll.batches.approveAll');
        Route::post('/payroll/batches/paid-all', [PayrollBatchController::class, 'markPaidAllApproved'])->middleware('can:mark batch paid')->name('payroll.batches.paidAll');

        Route::middleware(['can:view wallet'])->group(function () {
            Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
            Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->middleware('can:deposit wallet')->name('wallet.deposit');
        });

        Route::middleware(['can:view reports'])->group(function () {
            Route::get('/reports/employees', [HrmReportsController::class, 'employees'])->name('reports.employees');
            Route::get('/reports/employees.csv', [HrmReportsController::class, 'employeesCsv'])->name('reports.employees.csv');
            Route::get('/reports/leaves', [HrmReportsController::class, 'leaves'])->name('reports.leaves');
            Route::get('/reports/leaves.csv', [HrmReportsController::class, 'leavesCsv'])->name('reports.leaves.csv');
            Route::get('/reports/attendance', [HrmReportsController::class, 'attendance'])->name('reports.attendance');
            Route::get('/reports/payroll', [HrmReportsController::class, 'payroll'])->name('reports.payroll');
            Route::get('/reports/payroll.csv', [HrmReportsController::class, 'payrollCsv'])->name('reports.payroll.csv');
            Route::get('/reports/advances', [\App\Http\Controllers\ReportsController::class, 'advances'])->name('reports.advances');
            Route::get('/reports/advances.csv', [\App\Http\Controllers\ReportsController::class, 'advancesCsv'])->name('reports.advances.csv');
            Route::get('/reports/expenses', [HrmReportsController::class, 'expenses'])->name('reports.expenses');
            Route::get('/reports/expenses.csv', [HrmReportsController::class, 'expensesCsv'])->name('reports.expenses.csv');
            Route::get('/reports/payments', [HrmReportsController::class, 'payments'])->name('reports.payments');
            Route::get('/reports/payments.csv', [HrmReportsController::class, 'paymentsCsv'])->name('reports.payments.csv');
        });

        Route::middleware(['can:view organizations'])->group(function () {
            Route::get('/organizations', [\App\Http\Controllers\OrganizationController::class, 'index'])->name('organizations.index');
            Route::get('/organizations/create', [\App\Http\Controllers\OrganizationController::class, 'create'])->middleware('can:create organizations')->name('organizations.create');
            Route::post('/organizations', [\App\Http\Controllers\OrganizationController::class, 'store'])->middleware('can:create organizations')->name('organizations.store');
            Route::get('/organizations/{organization}/edit', [\App\Http\Controllers\OrganizationController::class, 'edit'])->middleware('can:edit organizations')->name('organizations.edit');
            Route::patch('/organizations/{organization}', [\App\Http\Controllers\OrganizationController::class, 'update'])->middleware('can:edit organizations')->name('organizations.update');
            Route::delete('/organizations/{organization}', [\App\Http\Controllers\OrganizationController::class, 'destroy'])->middleware('can:delete organizations')->name('organizations.destroy');
        });
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::middleware(['can:view users'])->group(function () {
            Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [\App\Http\Controllers\UserController::class, 'create'])->middleware('can:create users')->name('users.create');
            Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->middleware('can:create users')->name('users.store');
            Route::get('/users/{user}/edit', [\App\Http\Controllers\UserController::class, 'edit'])->middleware('can:edit users')->name('users.edit');
            Route::patch('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->middleware('can:edit users')->name('users.update');
            Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->middleware('can:delete users')->name('users.destroy');
        });

        // Role Management (granular)
        Route::get('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->middleware('can:view roles')->name('roles.index');
        Route::get('/roles/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->middleware('can:create roles')->name('roles.create');
        Route::post('/roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->middleware('can:create roles')->name('roles.store');
        Route::get('/roles/{role}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->middleware('can:edit roles')->name('roles.edit');
        Route::match(['put','patch'],'/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->middleware('can:edit roles')->name('roles.update');
        Route::delete('/roles/{role}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->middleware('can:delete roles')->name('roles.destroy');

        // Permission Management (granular)
        Route::get('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'index'])->middleware('can:view permissions')->name('permissions.index');
        Route::get('/permissions/create', [\App\Http\Controllers\Admin\PermissionController::class, 'create'])->middleware('can:create permissions')->name('permissions.create');
        Route::post('/permissions', [\App\Http\Controllers\Admin\PermissionController::class, 'store'])->middleware('can:create permissions')->name('permissions.store');
        Route::get('/permissions/{permission}/edit', [\App\Http\Controllers\Admin\PermissionController::class, 'edit'])->middleware('can:edit permissions')->name('permissions.edit');
        Route::match(['put','patch'],'/permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'update'])->middleware('can:edit permissions')->name('permissions.update');
        Route::delete('/permissions/{permission}', [\App\Http\Controllers\Admin\PermissionController::class, 'destroy'])->middleware('can:delete permissions')->name('permissions.destroy');

        // API Configurations (granular)
        Route::get('/api-configurations', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'index'])->middleware('can:view api configs')->name('api-configurations.index');
        Route::get('/api-configurations/create', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'create'])->middleware('can:create api configs')->name('api-configurations.create');
        Route::post('/api-configurations', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'store'])->middleware('can:create api configs')->name('api-configurations.store');
        Route::get('/api-configurations/{apiConfiguration}/edit', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'edit'])->middleware('can:edit api configs')->name('api-configurations.edit');
        Route::patch('/api-configurations/{apiConfiguration}', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'update'])->middleware('can:edit api configs')->name('api-configurations.update');
        Route::delete('/api-configurations/{apiConfiguration}', [\App\Http\Controllers\Admin\ApiConfigurationController::class, 'destroy'])->middleware('can:delete api configs')->name('api-configurations.destroy');
    });
});

require __DIR__.'/auth.php';
