<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ShiftScheduleService
{
    /** ZKTeco TIME string order: Minggu → Sabtu (maps to our day numbers). */
    private const ZKTECO_DAY_MAP = [7, 1, 2, 3, 4, 5, 6];

    public function deviceTimezoneId(Shift $shift): int
    {
        return min(max($shift->id, 1), 50);
    }

    public function buildZktecoTimeString(Shift $shift): string
    {
        $workDays = $shift->resolvedWorkDays();
        $start = str_replace(':', '', $shift->formattedStartTime());
        $end = str_replace(':', '', $shift->formattedEndTime());

        $segments = '';

        foreach (self::ZKTECO_DAY_MAP as $dayNumber) {
            if (in_array($dayNumber, $workDays, true)) {
                $segments .= $start.$end;
            } else {
                $segments .= '00000000';
            }
        }

        return $segments;
    }

    public function buildUserTzString(Shift $shift): string
    {
        $tzHex = str_pad(strtoupper(dechex($this->deviceTimezoneId($shift))), 4, '0', STR_PAD_LEFT);

        return '0000'.$tzHex.'00000000';
    }

    public function queueSyncShift(FingerprintDevice $device, Shift $shift): void
    {
        $tzId = $this->deviceTimezoneId($shift);

        $device->commands()->create([
            'command' => 'DATA UPDATE TIMEZONE TZID='.$tzId."\tTIME=".$this->buildZktecoTimeString($shift),
            'status' => 'pending',
        ]);
    }

    public function queueSyncShiftsForDevice(FingerprintDevice $device): int
    {
        $shifts = $this->shiftsForDevice($device);

        foreach ($shifts as $shift) {
            $this->queueSyncShift($device, $shift);
        }

        return $shifts->count();
    }

    public function queueSyncShiftsForBranch(?int $branchId): int
    {
        $devicesQuery = FingerprintDevice::query()->where('is_active', true);

        if ($branchId !== null) {
            $devicesQuery->where('branch_id', $branchId);
        }

        $devices = $devicesQuery->get();
        $count = 0;

        foreach ($devices as $device) {
            $count += $this->queueSyncShiftsForDevice($device);
        }

        return $count;
    }

    /**
     * Tentukan masuk/pulang bergantian per hari: tap 1 = masuk, 2 = pulang, 3 = masuk, dst.
     * Reset otomatis saat tanggal berubah (whereDate).
     */
    public function resolveAlternatingPunchType(Employee $employee, Carbon $attendedAt): AttendanceType
    {
        $count = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attended_at', $attendedAt->toDateString())
            ->whereIn('type', [AttendanceType::CheckIn, AttendanceType::CheckOut])
            ->count();

        return $count % 2 === 0
            ? AttendanceType::CheckIn
            : AttendanceType::CheckOut;
    }

    /**
     * @return array{
     *     type: AttendanceType,
     *     status: AttendanceStatus,
     *     is_late: bool,
     *     late_minutes: int|null,
     *     notes: string
     * }
     */
    public function evaluatePunch(Employee $employee, Carbon $punchedAt): array
    {
        $employee->loadMissing('shift');
        $shift = $employee->shift;
        $type = $this->resolveAlternatingPunchType($employee, $punchedAt);
        $notes = ['Absensi via mesin fingerprint', $type->label()];

        if ($shift === null) {
            return [
                'type' => $type,
                'status' => AttendanceStatus::Valid,
                'is_late' => false,
                'late_minutes' => null,
                'notes' => implode('. ', $notes),
            ];
        }

        $notes[] = 'Jadwal: '.$shift->name.' ('.$shift->formattedStartTime().'–'.$shift->formattedEndTime().')';

        if (! in_array($punchedAt->dayOfWeekIso, $shift->resolvedWorkDays(), true)) {
            $notes[] = 'Di luar hari kerja jadwal';
        }

        $lateness = $this->evaluateLateness($employee, $punchedAt, $type);

        if ($lateness['note']) {
            $notes[] = $lateness['note'];
        }

        return [
            'type' => $type,
            'status' => $lateness['is_late'] ? AttendanceStatus::Late : AttendanceStatus::Valid,
            'is_late' => $lateness['is_late'],
            'late_minutes' => $lateness['late_minutes'],
            'notes' => implode('. ', $notes),
        ];
    }

    /**
     * @return array{
     *     status: AttendanceStatus,
     *     is_late: bool,
     *     late_minutes: int|null,
     *     note: string|null
     * }
     */
    public function evaluateAttendanceRecord(
        Employee $employee,
        Carbon $attendedAt,
        AttendanceType $type,
        AttendanceStatus $verificationStatus,
    ): array {
        if (! $verificationStatus->isSuccessful()) {
            return [
                'status' => $verificationStatus,
                'is_late' => false,
                'late_minutes' => null,
                'note' => null,
            ];
        }

        $lateness = $this->evaluateLateness($employee, $attendedAt, $type);

        return [
            'status' => $lateness['is_late'] ? AttendanceStatus::Late : AttendanceStatus::Valid,
            'is_late' => $lateness['is_late'],
            'late_minutes' => $lateness['late_minutes'],
            'note' => $lateness['note'],
        ];
    }

    /**
     * @return array{is_late: bool, late_minutes: int|null, note: string|null}
     */
    public function evaluateLateness(Employee $employee, Carbon $attendedAt, AttendanceType $type): array
    {
        if ($type !== AttendanceType::CheckIn) {
            return ['is_late' => false, 'late_minutes' => null, 'note' => null];
        }

        $employee->loadMissing('shift');
        $shift = $employee->shift;

        if ($shift === null) {
            return ['is_late' => false, 'late_minutes' => null, 'note' => null];
        }

        $start = Carbon::parse(
            $attendedAt->toDateString().' '.$shift->formattedStartTime(),
            config('app.timezone'),
        );
        $deadline = $start->copy()->addMinutes($shift->late_tolerance_minutes);

        if ($attendedAt->lte($deadline)) {
            return ['is_late' => false, 'late_minutes' => null, 'note' => null];
        }

        $lateMinutes = (int) $start->diffInMinutes($attendedAt);

        return [
            'is_late' => true,
            'late_minutes' => $lateMinutes,
            'note' => "Terlambat {$lateMinutes} menit (jadwal masuk {$shift->formattedStartTime()}, toleransi {$shift->late_tolerance_minutes} mnt)",
        ];
    }

    public function recalculateStoredAttendance(Attendance $attendance): bool
    {
        if ($attendance->type === AttendanceType::Leave) {
            return false;
        }

        if (! $attendance->status->isSuccessful()) {
            return false;
        }

        $attendance->loadMissing('employee.shift');

        $schedule = $this->evaluateAttendanceRecord(
            $attendance->employee,
            $attendance->attended_at,
            $attendance->type,
            AttendanceStatus::Valid,
        );

        $attendance->fill([
            'status' => $schedule['status'],
            'is_late' => $schedule['is_late'],
            'late_minutes' => $schedule['late_minutes'],
        ]);

        if (! $attendance->isDirty()) {
            return false;
        }

        $attendance->save();

        return true;
    }

    /** @return array{checked: int, updated: int} */
    public function recalculateStoredAttendances(?Carbon $from = null, ?Carbon $until = null): array
    {
        $query = Attendance::query()
            ->with('employee.shift')
            ->whereIn('type', [AttendanceType::CheckIn, AttendanceType::CheckOut])
            ->whereIn('status', [
                AttendanceStatus::Valid,
                AttendanceStatus::Late,
            ]);

        if ($from !== null) {
            $query->whereDate('attended_at', '>=', $from->toDateString());
        }

        if ($until !== null) {
            $query->whereDate('attended_at', '<=', $until->toDateString());
        }

        $checked = 0;
        $updated = 0;

        $query->orderBy('id')->chunkById(200, function ($attendances) use (&$checked, &$updated) {
            foreach ($attendances as $attendance) {
                $checked++;

                if ($this->recalculateStoredAttendance($attendance)) {
                    $updated++;
                }
            }
        });

        return compact('checked', 'updated');
    }

    /** @return Collection<int, Shift> */
    private function shiftsForDevice(FingerprintDevice $device): Collection
    {
        return Shift::query()
            ->where('is_active', true)
            ->where(function ($query) use ($device) {
                $query->whereNull('branch_id');

                if ($device->branch_id) {
                    $query->orWhere('branch_id', $device->branch_id);
                }
            })
            ->orderBy('id')
            ->get();
    }

}
