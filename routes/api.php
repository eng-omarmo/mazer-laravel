<?php

use App\Http\Controllers\AttendanceApiController;
use App\Http\Controllers\Admin\PermissionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/attendance-sync', [AttendanceApiController::class, 'sync']);

Route::middleware(['auth:sanctum', 'permission:view permissions'])->group(function () {
    Route::get('/permissions', [PermissionApiController::class, 'permissions']);
    Route::get('/roles', [PermissionApiController::class, 'roles']);
});
Route::middleware(['auth:sanctum', 'permission:edit roles'])->group(function () {
    Route::post('/roles/{role}/permissions', [PermissionApiController::class, 'syncRolePermissions']);
});
