<?php

use App\Http\Controllers\Web\AppBrandingController;
use App\Http\Controllers\Web\AnnouncementController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AttendanceManageController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\BranchLocationController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DepartmentController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\EmployeeShiftController;
use App\Http\Controllers\Web\FaceEnrollmentController;
use App\Http\Controllers\Web\FingerprintDeviceController;
use App\Http\Controllers\Web\HolidayController;
use App\Http\Controllers\Web\LeaveApprovalController;
use App\Http\Controllers\Web\LeaveProofController;
use App\Http\Controllers\Web\LeaveRequestController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\PayrollController;
use App\Http\Controllers\Web\PotonganController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\ShiftController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/branding/logo', [AppBrandingController::class, 'logo'])->name('branding.logo');

Route::post('/locale', LocaleController::class)->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('permission:attendance.scan')->group(function () {
        Route::get('/attendance/scan', [AttendanceController::class, 'scanForm'])->name('attendance.scan');
        Route::post('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan.store');
    });

    Route::middleware('permission:attendance.view_all|attendance.view_own')->group(function () {
        Route::get('/attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    });

    Route::middleware('permission:attendance.manage')->group(function () {
        Route::get('/attendances/manage', [AttendanceManageController::class, 'index'])->name('attendances.manage');
        Route::get('/attendances/create', [AttendanceManageController::class, 'create'])->name('attendances.create');
        Route::post('/attendances/manual', [AttendanceManageController::class, 'store'])->name('attendances.manual.store');
        Route::patch('/attendances/{attendance}/status', [AttendanceManageController::class, 'updateStatus'])->name('attendances.status.update');
    });

    Route::middleware('permission:fingerprint.manage')->group(function () {
        Route::get('/fingerprint-devices', [FingerprintDeviceController::class, 'index'])->name('fingerprint-devices.index');
        Route::get('/fingerprint-devices/{fingerprintDevice}/edit', [FingerprintDeviceController::class, 'edit'])->name('fingerprint-devices.edit');
        Route::put('/fingerprint-devices/{fingerprintDevice}', [FingerprintDeviceController::class, 'update'])->name('fingerprint-devices.update');
        Route::post('/fingerprint-devices/{fingerprintDevice}/sync-employees', [FingerprintDeviceController::class, 'syncEmployees'])->name('fingerprint-devices.sync-employees');
        Route::post('/fingerprint-devices/{fingerprintDevice}/sync-shifts', [FingerprintDeviceController::class, 'syncShifts'])->name('fingerprint-devices.sync-shifts');
        Route::post('/fingerprint-devices/{fingerprintDevice}/sync-all', [FingerprintDeviceController::class, 'syncAll'])->name('fingerprint-devices.sync-all');
        Route::post('/fingerprint-devices/{fingerprintDevice}/pull-logs', [FingerprintDeviceController::class, 'pullLogs'])->name('fingerprint-devices.pull-logs');
        Route::get('/fingerprint-devices/log-sync-status', [FingerprintDeviceController::class, 'indexLogSyncStatus'])->name('fingerprint-devices.index-log-sync-status');
        Route::get('/fingerprint-devices/{fingerprintDevice}/log-sync-status', [FingerprintDeviceController::class, 'logSyncStatus'])->name('fingerprint-devices.log-sync-status');
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });

    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
        Route::post('/roles/action-modules', [RoleController::class, 'storeActionModule'])->name('roles.action-modules.store');
        Route::post('/roles/action-modules/{moduleKey}/actions', [RoleController::class, 'storeModuleAction'])->name('roles.module-actions.store');
    });

    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    Route::middleware('permission:branches.manage')->group(function () {
        Route::resource('branches', BranchController::class);
        Route::get('/branches/{branch}/locations/create', [BranchLocationController::class, 'create'])->name('branch-locations.create');
        Route::post('/branches/{branch}/locations', [BranchLocationController::class, 'store'])->name('branch-locations.store');
        Route::put('/branch-locations/{branchLocation}', [BranchLocationController::class, 'update'])->name('branch-locations.update');
        Route::delete('/branch-locations/{branchLocation}', [BranchLocationController::class, 'destroy'])->name('branch-locations.destroy');
    });

    Route::middleware('permission:departments.manage')->group(function () {
        Route::resource('departments', DepartmentController::class)->except(['show']);
    });

    Route::middleware('permission:positions.manage')->group(function () {
        Route::resource('positions', PositionController::class)->except(['show']);
    });

    Route::middleware('permission:employees.manage')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('/employees/{employee}/attendances', [AttendanceController::class, 'employeeHistory'])->name('employees.attendances');
    });

    Route::middleware('permission:faces.enroll')->group(function () {
        Route::get('/employees/{employee}/faces/enroll', [FaceEnrollmentController::class, 'create'])->name('faces.enroll');
        Route::post('/employees/{employee}/faces', [FaceEnrollmentController::class, 'store'])->name('faces.store');
    });

    Route::middleware('permission:shifts.manage')->group(function () {
        Route::resource('shifts', ShiftController::class)->except(['show']);
        Route::get('/employee-shifts', [EmployeeShiftController::class, 'index'])->name('employee-shifts.index');
        Route::put('/employee-shifts/{employee}', [EmployeeShiftController::class, 'update'])->name('employee-shifts.update');
        Route::post('/employee-shifts/bulk', [EmployeeShiftController::class, 'bulkUpdate'])->name('employee-shifts.bulk');
    });

    Route::middleware('permission:holidays.manage')->group(function () {
        Route::resource('holidays', HolidayController::class)->except(['show']);
    });

    Route::middleware('permission:leave.request|leave.view_own')->group(function () {
        Route::get('/leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/leaves/{leave}/proof', [LeaveProofController::class, 'show'])->name('leaves.proof');
    });

    Route::middleware('permission:leave.request')->group(function () {
        Route::get('/leaves/create', [LeaveRequestController::class, 'create'])->name('leaves.create');
        Route::post('/leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
    });

    Route::middleware('permission:leave.approve')->group(function () {
        Route::get('/leave-approvals', [LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
        Route::post('/leave-approvals/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
        Route::post('/leave-approvals/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');
    });

    Route::middleware('permission:payroll.manage|payroll.view_own')->group(function () {
        Route::get('/payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
        Route::get('/payrolls/{payroll}/items/{item}/deductions', [PayrollController::class, 'deductionDetails'])->name('payrolls.items.deductions');
    });

    Route::middleware('permission:payroll.manage')->group(function () {
        Route::get('/potongan', [PotonganController::class, 'index'])->name('potongan.index');
        Route::put('/potongan', [PotonganController::class, 'update'])->name('potongan.update');
        Route::post('/payrolls', [PayrollController::class, 'store'])->name('payrolls.store');
        Route::get('/payrolls/{payroll}', [PayrollController::class, 'show'])->name('payrolls.show');
        Route::post('/payrolls/{payroll}/regenerate', [PayrollController::class, 'regenerate'])->name('payrolls.regenerate');
        Route::post('/payrolls/{payroll}/finalize', [PayrollController::class, 'finalize'])->name('payrolls.finalize');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    Route::middleware('permission:announcements.manage')->group(function () {
        Route::resource('pengumuman', AnnouncementController::class)
            ->names('announcements')
            ->parameters(['pengumuman' => 'announcement'])
            ->except(['show']);
    });
});
