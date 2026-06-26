<?php

namespace App\Http\Controllers\Web;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Enums\AttendanceStatus;
use App\Services\AnnouncementService;
use App\Services\DashboardAttendanceChartService;
use App\Services\LeaveBadgeService;
use App\Services\PayrollSlipBadgeService;
use App\Services\ProfileFaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends WebController
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $branchIds = $this->manageableBranchIds($request);

        $statsQuery = fn ($model) => $branchIds === null
            ? $model::query()
            : $model::query()->whereIn('branch_id', $branchIds);

        $attendanceTodayQuery = Attendance::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($user->role->value === 'employee' && $user->employee, fn ($q) => $q->where('employee_id', $user->employee->id))
            ->whereDate('attended_at', today());

        $stats = [
            'branches' => $branchIds === null
                ? Branch::query()->where('is_active', true)->count()
                : count($branchIds),
            'employees' => $statsQuery(Employee::class)->where('is_active', true)->count(),
            'attendances_today' => (clone $attendanceTodayQuery)->count(),
            'on_time_today' => (clone $attendanceTodayQuery)->where('status', AttendanceStatus::Valid)->count(),
            'late_today' => (clone $attendanceTodayQuery)->where('status', AttendanceStatus::Late)->count(),
            'invalid_today' => (clone $attendanceTodayQuery)->whereIn('status', [
                AttendanceStatus::InvalidFace,
                AttendanceStatus::InvalidLocation,
                AttendanceStatus::InvalidBoth,
            ])->count(),
        ];

        $badgeService = app(LeaveBadgeService::class);
        $payrollBadgeService = app(PayrollSlipBadgeService::class);
        $pendingLeaveApprovalCount = $badgeService->pendingApprovalCount($user);
        $pendingOwnLeaveCount = $badgeService->pendingOwnCount($user);
        $payrollSignatureNotifications = [
            'count' => $payrollBadgeService->pendingApprovalCount($user),
            'recent' => $payrollBadgeService->recentPendingApprovals($user),
        ];
        $pendingPayrollSignatureCount = $payrollSignatureNotifications['count'];

        $chartService = app(DashboardAttendanceChartService::class);
        $employeeId = $user->role->value === 'employee' && $user->employee
            ? $user->employee->id
            : null;
        $attendanceChart = $chartService->build($branchIds, $employeeId);
        $showAttendanceChart = $user->role->value !== 'employee';

        $announcements = app(AnnouncementService::class)->forDashboard($user);
        $needsFaceEnrollment = app(ProfileFaceService::class)->needsEnrollment($user);

        return view('dashboard.index', compact(
            'stats',
            'pendingLeaveApprovalCount',
            'pendingOwnLeaveCount',
            'payrollSignatureNotifications',
            'pendingPayrollSignatureCount',
            'attendanceChart',
            'showAttendanceChart',
            'announcements',
            'needsFaceEnrollment',
        ));
    }
}
