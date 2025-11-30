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
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('hrm')->name('hrm.')->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');

        Route::get('/verification', [DocumentVerificationController::class, 'index'])->name('verification.index');
        Route::post('/verification/{document}/approve', [DocumentVerificationController::class, 'approve'])->name('verification.approve');
        Route::post('/verification/{document}/reject', [DocumentVerificationController::class, 'reject'])->name('verification.reject');

        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{department}', [DepartmentController::class, 'show'])->name('departments.show');
        Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::patch('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
        Route::get('/leave/create', [LeaveController::class, 'create'])->name('leave.create');
        Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
        Route::get('/leave/{leave}/edit', [LeaveController::class, 'edit'])->name('leave.edit');
        Route::patch('/leave/{leave}', [LeaveController::class, 'update'])->name('leave.update');
        Route::delete('/leave/{leave}', [LeaveController::class, 'destroy'])->name('leave.destroy');
        Route::post('/leave/{leave}/approve', [LeaveController::class, 'approve'])->name('leave.approve');
        Route::post('/leave/{leave}/reject', [LeaveController::class, 'reject'])->name('leave.reject');

        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
        Route::post('/payroll', [PayrollController::class, 'store'])->name('payroll.store');
        Route::get('/payroll/{payroll}/edit', [PayrollController::class, 'edit'])->name('payroll.edit');
        Route::patch('/payroll/{payroll}', [PayrollController::class, 'update'])->name('payroll.update');
        Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
        Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
        Route::post('/payroll/{payroll}/paid', [PayrollController::class, 'markPaid'])->name('payroll.paid');

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/{log}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::patch('/attendance/{log}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::get('/attendance/summary', [AttendanceController::class, 'summary'])->name('attendance.summary');
        Route::get('/attendance/export/csv', [AttendanceController::class, 'exportCsv'])->name('attendance.export.csv');
        Route::get('/attendance/my', [AttendanceController::class, 'myHistory'])->name('attendance.my');

        Route::get('/advances', [\App\Http\Controllers\EmployeeAdvanceController::class, 'index'])->name('advances.index');
        Route::get('/advances/create', [\App\Http\Controllers\EmployeeAdvanceController::class, 'create'])->name('advances.create');
        Route::post('/advances', [\App\Http\Controllers\EmployeeAdvanceController::class, 'store'])->name('advances.store');
        Route::post('/advances/{advance}/approve', [\App\Http\Controllers\EmployeeAdvanceController::class, 'approve'])->name('advances.approve');
        Route::post('/advances/{advance}/paid', [\App\Http\Controllers\EmployeeAdvanceController::class, 'markPaid'])->name('advances.paid');
        Route::get('/advances/{advance}', [\App\Http\Controllers\EmployeeAdvanceController::class, 'show'])->name('advances.show');
        Route::post('/advances/{advance}/repay', [\App\Http\Controllers\EmployeeAdvanceController::class, 'repay'])->name('advances.repay');
        Route::get('/advances/receipt/{transaction}', [\App\Http\Controllers\EmployeeAdvanceController::class, 'receipt'])->name('advances.receipt');

        Route::get('/payroll/batches', [PayrollBatchController::class, 'index'])->name('payroll.batches.index');
        Route::get('/payroll/batches/create', [PayrollBatchController::class, 'create'])->name('payroll.batches.create');
        Route::post('/payroll/batches', [PayrollBatchController::class, 'store'])->name('payroll.batches.store');
        Route::get('/payroll/batches/{batch}', [PayrollBatchController::class, 'show'])->name('payroll.batches.show');
        Route::patch('/payroll/batches/{batch}', [PayrollBatchController::class, 'update'])->name('payroll.batches.update');
        Route::post('/payroll/batches/{batch}/submit', [PayrollBatchController::class, 'submit'])->name('payroll.batches.submit');
        Route::post('/payroll/batches/{batch}/approve', [PayrollBatchController::class, 'approve'])->name('payroll.batches.approve');
        Route::post('/payroll/batches/{batch}/reject', [PayrollBatchController::class, 'reject'])->name('payroll.batches.reject');
        Route::post('/payroll/batches/{batch}/paid', [PayrollBatchController::class, 'markPaid'])->name('payroll.batches.paid');
        Route::post('/payroll/batches/approve-all', [PayrollBatchController::class, 'approveAllPending'])->name('payroll.batches.approveAll');
        Route::post('/payroll/batches/paid-all', [PayrollBatchController::class, 'markPaidAllApproved'])->name('payroll.batches.paidAll');

        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');

        Route::get('/reports/employees', [HrmReportsController::class, 'employees'])->name('reports.employees');
        Route::get('/reports/employees.csv', [HrmReportsController::class, 'employeesCsv'])->name('reports.employees.csv');
        Route::get('/reports/leaves', [HrmReportsController::class, 'leaves'])->name('reports.leaves');
        Route::get('/reports/leaves.csv', [HrmReportsController::class, 'leavesCsv'])->name('reports.leaves.csv');
        Route::get('/reports/attendance', [HrmReportsController::class, 'attendance'])->name('reports.attendance');
        Route::get('/reports/payroll', [HrmReportsController::class, 'payroll'])->name('reports.payroll');
        Route::get('/reports/advances', [\App\Http\Controllers\ReportsController::class, 'advances'])->name('reports.advances');
        Route::get('/reports/advances.csv', [\App\Http\Controllers\ReportsController::class, 'advancesCsv'])->name('reports.advances.csv');

                Route::get('/organizations', [\App\Http\Controllers\OrganizationController::class, 'index'])->name('organizations.index');
        Route::get('/organizations/create', [\App\Http\Controllers\OrganizationController::class, 'create'])->name('organizations.create');
        Route::post('/organizations', [\App\Http\Controllers\OrganizationController::class, 'store'])->name('organizations.store');
        Route::get('/organizations/{organization}/edit', [\App\Http\Controllers\OrganizationController::class, 'edit'])->name('organizations.edit');
        Route::patch('/organizations/{organization}', [\App\Http\Controllers\OrganizationController::class, 'update'])->name('organizations.update');
        Route::delete('/organizations/{organization}', [\App\Http\Controllers\OrganizationController::class, 'destroy'])->name('organizations.destroy');

    });

});

require __DIR__.'/auth.php';
