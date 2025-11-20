<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\DepartmentController;
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
    });
});

require __DIR__.'/auth.php';
