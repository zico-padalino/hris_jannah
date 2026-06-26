<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\BranchLocationController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\FaceEnrollmentController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::post('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
    Route::post('/attendance/scan-self', [AttendanceController::class, 'scanSelf'])->name('attendance.scan-self');
    Route::get('/attendance/my', [AttendanceController::class, 'myHistory'])->name('attendance.my');
    Route::get('/leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
    Route::get('/payroll/my', [PayrollController::class, 'myPayroll'])->name('payroll.my');

    Route::middleware(EnsureRole::class.':super_admin,hr,branch_admin')->group(function () {
        Route::apiResource('branches', BranchController::class);

        Route::get('/branches/{branch}/locations', [BranchLocationController::class, 'index'])->name('branches.locations.index');
        Route::post('/branches/{branch}/locations', [BranchLocationController::class, 'store'])->name('branches.locations.store');
        Route::put('/branch-locations/{branchLocation}', [BranchLocationController::class, 'update'])->name('branch-locations.update');
        Route::delete('/branch-locations/{branchLocation}', [BranchLocationController::class, 'destroy'])->name('branch-locations.destroy');

        Route::get('/branches/{branch}/departments', [DepartmentController::class, 'index'])->name('branches.departments.index');
        Route::post('/branches/{branch}/departments', [DepartmentController::class, 'store'])->name('branches.departments.store');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        Route::apiResource('employees', EmployeeController::class);
        Route::get('/branches/{branch}/employees', [EmployeeController::class, 'byBranch'])->name('branches.employees');

        Route::get('/employees/{employee}/faces', [FaceEnrollmentController::class, 'index'])->name('employees.faces.index');
        Route::post('/employees/{employee}/faces', [FaceEnrollmentController::class, 'store'])->name('employees.faces.store');
        Route::delete('/employees/{employee}/faces/{face}', [FaceEnrollmentController::class, 'destroy'])->name('employees.faces.destroy');

        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
        Route::get('/employees/{employee}/attendances', [AttendanceController::class, 'employeeHistory'])->name('employees.attendances');
    });
});
