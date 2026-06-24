<?php

namespace App\Data;

use App\Enums\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceDayGroup
{
    /** @param Collection<int, Attendance> $records */
    public function __construct(
        public Employee $employee,
        public Carbon $date,
        public Collection $records,
    ) {
        $this->records = $records->sortBy('attended_at')->values();
    }

    public function checkIn(): ?Attendance
    {
        return $this->records->first(fn (Attendance $record) => $record->type === AttendanceType::CheckIn);
    }

    public function checkOut(): ?Attendance
    {
        return $this->records->first(fn (Attendance $record) => $record->type === AttendanceType::CheckOut);
    }

    public function leaveRecord(): ?Attendance
    {
        return $this->records->first(fn (Attendance $record) => $record->type === AttendanceType::Leave);
    }

    public function branchLabel(): string
    {
        $branch = $this->records->first()?->branch?->name ?? '-';
        $location = $this->records->first(fn (Attendance $record) => $record->branchLocation)?->branchLocation?->name;

        return $location ? "{$branch} · {$location}" : $branch;
    }

    public function totalDeduction(): float
    {
        return (float) $this->records->sum(fn (Attendance $record) => $record->payrollDeductionAmount());
    }

    /** @return list<Attendance> */
    public function displayRecords(): array
    {
        $ordered = [];

        foreach ([AttendanceType::Leave, AttendanceType::CheckIn, AttendanceType::CheckOut] as $type) {
            $record = $this->records->first(fn (Attendance $item) => $item->type === $type);

            if ($record) {
                $ordered[] = $record;
            }
        }

        foreach ($this->records as $record) {
            if (! in_array($record, $ordered, true)) {
                $ordered[] = $record;
            }
        }

        return $ordered;
    }
}
