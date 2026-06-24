<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\PayrollStatus;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(private readonly PayrollDeductionConfig $deductionConfig) {}

    public function generate(PayrollPeriod $period): PayrollPeriod
    {
        return DB::transaction(function () use ($period) {
            $employees = Employee::query()
                ->where('is_active', true)
                ->when($period->branch_id, fn ($q) => $q->where('branch_id', $period->branch_id))
                ->get();

            foreach ($employees as $employee) {
                $monthAttendances = $employee->attendances()
                    ->whereYear('attended_at', $period->year)
                    ->whereMonth('attended_at', $period->month)
                    ->get();

                $lateCount = $monthAttendances
                    ->filter(fn ($attendance) => $attendance->status === AttendanceStatus::Late)
                    ->count();

                $invalidCount = $monthAttendances
                    ->filter(fn ($attendance) => in_array($attendance->status, [
                        AttendanceStatus::InvalidFace,
                        AttendanceStatus::InvalidLocation,
                        AttendanceStatus::InvalidBoth,
                    ], true))
                    ->count();

                $deductibleCount = $lateCount + $invalidCount;
                $baseSalary = (float) $employee->base_salary;
                $allowances = 0.0;
                $breakdown = $this->deductionConfig->calculate($baseSalary, $allowances, $deductibleCount);
                $netSalary = max(0, $baseSalary + $allowances - $breakdown['total']);

                $period->items()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    [
                        'base_salary' => $baseSalary,
                        'allowances' => $allowances,
                        'deductions' => $breakdown['total'],
                        'net_salary' => $netSalary,
                        'notes' => $breakdown['notes'] !== [] ? implode(' · ', $breakdown['notes']) : null,
                    ]
                );
            }

            return $period->fresh('items.employee');
        });
    }

    public function finalize(PayrollPeriod $period): PayrollPeriod
    {
        $period->update(['status' => PayrollStatus::Finalized]);

        return $period->fresh();
    }

    /** @return Collection<int, \App\Models\Attendance> */
    public function deductibleAttendances(Employee $employee, PayrollPeriod $period): Collection
    {
        return $employee->attendances()
            ->with(['branch', 'employee.shift'])
            ->whereYear('attended_at', $period->year)
            ->whereMonth('attended_at', $period->month)
            ->whereIn('status', [
                AttendanceStatus::Late->value,
                AttendanceStatus::InvalidFace->value,
                AttendanceStatus::InvalidLocation->value,
                AttendanceStatus::InvalidBoth->value,
            ])
            ->orderBy('attended_at')
            ->get();
    }
}
