<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HRM\DepartmentController;
use App\Http\Controllers\HRM\EmployeeController;
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

    Route::prefix('hrm')->group(function () {
        Route::resource('departments', DepartmentController::class)->names('hrm.departments');
        Route::resource('employees', EmployeeController::class)->names('hrm.employees');
        Route::view('/attendance', 'hrm.attendance');
        Route::view('/leave', 'hrm.leave');
        Route::view('/payroll', 'hrm.payroll');
        Route::view('/recruitment', 'hrm.recruitment');
        Route::view('/performance', 'hrm.performance');
        Route::view('/reports', 'hrm.reports');
        Route::view('/settings', 'hrm.settings');
    });
});

require __DIR__.'/auth.php';
