<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Support\Collection;

class ReportService
{
    public function attendanceSummary(?array $branchIds, ?string $month = null): Collection
    {
        $month = $month ?? now()->format('Y-m');

        return Attendance::query()
            ->selectRaw("branch_id, COUNT(*) as total, SUM(CASE WHEN status = 'valid' THEN 1 ELSE 0 END) as valid_count, SUM(CASE WHEN status != 'valid' THEN 1 ELSE 0 END) as invalid_count")
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->whereRaw("DATE_FORMAT(attended_at, '%Y-%m') = ?", [$month])
            ->groupBy('branch_id')
            ->with('branch')
            ->get();
    }

    public function employeeAttendanceReport(int $employeeId, ?string $month = null): array
    {
        $month = $month ?? now()->format('Y-m');

        $query = Attendance::query()
            ->where('employee_id', $employeeId)
            ->whereRaw("DATE_FORMAT(attended_at, '%Y-%m') = ?", [$month]);

        return [
            'total' => (clone $query)->count(),
            'valid' => (clone $query)->where('status', 'valid')->count(),
            'invalid' => (clone $query)->where('status', '!=', 'valid')->count(),
            'records' => $query->latest('attended_at')->get(),
        ];
    }

    public function dashboardStats(?array $branchIds): array
    {
        return [
            'branches' => $branchIds === null
                ? Branch::query()->where('is_active', true)->count()
                : count($branchIds),
            'employees' => Employee::query()
                ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
                ->where('is_active', true)->count(),
            'attendances_today' => Attendance::query()
                ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
                ->whereDate('attended_at', today())->count(),
            'pending_leaves' => LeaveRequest::query()
                ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
                ->where('status', 'pending')->count(),
        ];
    }
}
