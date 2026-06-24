<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\AttendanceType;
use App\Enums\LeaveStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;

class DashboardAttendanceChartService
{
    /**
     * @return array{
     *     labels: list<string>,
     *     dates: list<string>,
     *     series: array{masuk: list<int>, izin: list<int>, ga_masuk: list<int>, telat: list<int>},
     *     today: array{masuk: int, izin: int, ga_masuk: int, telat: int, total_employees: int}
     * }
     */
    public function build(?array $branchIds, ?int $employeeId = null, int $days = 7): array
    {
        $end = today();
        $start = today()->subDays($days - 1);

        $employeeQuery = Employee::query()->where('is_active', true);
        if ($branchIds !== null) {
            $employeeQuery->whereIn('branch_id', $branchIds);
        }
        if ($employeeId !== null) {
            $employeeQuery->where('id', $employeeId);
        }
        $employeeIds = $employeeQuery->pluck('id')->all();

        $checkIns = Attendance::query()
            ->where('type', AttendanceType::CheckIn)
            ->whereBetween('attended_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($employeeId !== null, fn ($q) => $q->where('employee_id', $employeeId))
            ->orderBy('attended_at')
            ->get(['employee_id', 'attended_at', 'status']);

        $checkInByDay = [];
        foreach ($checkIns as $record) {
            $dateKey = $record->attended_at->toDateString();
            if (! isset($checkInByDay[$dateKey][$record->employee_id])) {
                $checkInByDay[$dateKey][$record->employee_id] = $record->status;
            }
        }

        $leaveAttendanceByDay = [];
        Attendance::query()
            ->where('type', AttendanceType::Leave)
            ->whereBetween('attended_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($employeeId !== null, fn ($q) => $q->where('employee_id', $employeeId))
            ->get(['employee_id', 'attended_at'])
            ->each(function (Attendance $record) use (&$leaveAttendanceByDay): void {
                $dateKey = $record->attended_at->toDateString();
                $leaveAttendanceByDay[$dateKey][$record->employee_id] = true;
            });

        $leaveByDay = [];
        LeaveRequest::query()
            ->where('status', LeaveStatus::Approved)
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($employeeId !== null, fn ($q) => $q->where('employee_id', $employeeId))
            ->get(['employee_id', 'start_date', 'end_date'])
            ->each(function (LeaveRequest $leave) use (&$leaveByDay, $start, $end): void {
                $from = $leave->start_date->greaterThan($start) ? $leave->start_date->copy() : $start->copy();
                $to = $leave->end_date->lessThan($end) ? $leave->end_date->copy() : $end->copy();

                for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                    $leaveByDay[$date->toDateString()][$leave->employee_id] = true;
                }
            });

        $labels = [];
        $dates = [];
        $series = [
            'masuk' => [],
            'izin' => [],
            'ga_masuk' => [],
            'telat' => [],
        ];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateKey = $date->toDateString();
            $dayCounts = $this->countForDate(
                $employeeIds,
                $checkInByDay[$dateKey] ?? [],
                $leaveByDay[$dateKey] ?? [],
                $leaveAttendanceByDay[$dateKey] ?? [],
            );

            $labels[] = $date->locale('id')->translatedFormat('D');
            $dates[] = $dateKey;
            foreach (array_keys($series) as $key) {
                $series[$key][] = $dayCounts[$key];
            }
        }

        $todayKey = today()->toDateString();
        $todayCounts = $this->countForDate(
            $employeeIds,
            $checkInByDay[$todayKey] ?? [],
            $leaveByDay[$todayKey] ?? [],
            $leaveAttendanceByDay[$todayKey] ?? [],
        );

        return [
            'labels' => $labels,
            'dates' => $dates,
            'series' => $series,
            'today' => [
                ...$todayCounts,
                'total_employees' => count($employeeIds),
            ],
        ];
    }

    /**
     * @param  list<int>  $employeeIds
     * @param  array<int, AttendanceStatus>  $checkIns
     * @param  array<int, bool>  $approvedLeave
     * @param  array<int, bool>  $leaveAttendance
     * @return array{masuk: int, izin: int, ga_masuk: int, telat: int}
     */
    private function countForDate(array $employeeIds, array $checkIns, array $approvedLeave, array $leaveAttendance): array
    {
        $counts = [
            'masuk' => 0,
            'izin' => 0,
            'ga_masuk' => 0,
            'telat' => 0,
        ];

        foreach ($employeeIds as $employeeId) {
            if (isset($approvedLeave[$employeeId]) || isset($leaveAttendance[$employeeId])) {
                $counts['izin']++;

                continue;
            }

            $status = $checkIns[$employeeId] ?? null;

            if ($status === null) {
                $counts['ga_masuk']++;

                continue;
            }

            if ($status === AttendanceStatus::Late) {
                $counts['telat']++;

                continue;
            }

            $counts['masuk']++;
        }

        return $counts;
    }
}
