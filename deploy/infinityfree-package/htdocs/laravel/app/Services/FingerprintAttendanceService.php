<?php

namespace App\Services;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use App\Models\FingerprintLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FingerprintAttendanceService
{
    public function __construct(
        private readonly ShiftScheduleService $shiftScheduleService,
        private readonly AttendanceMethodSettingsService $attendanceMethods,
    ) {}
    /**
     * @return array{processed: int, skipped: int, failed: int}
     */
    public function processAttlogPayload(FingerprintDevice $device, string $payload): array
    {
        $stats = ['processed' => 0, 'skipped' => 0, 'failed' => 0];
        $entries = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($payload)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parsed = $this->parseAttlogLine($line);
            if ($parsed === null) {
                $stats['failed']++;
                continue;
            }

            $entries[] = ['parsed' => $parsed, 'raw' => $line];
        }

        usort(
            $entries,
            fn (array $a, array $b) => $a['parsed']['punched_at'] <=> $b['parsed']['punched_at'],
        );

        foreach ($entries as $entry) {
            $result = $this->processLog($device, $entry['parsed'], $entry['raw']);

            match ($result) {
                'processed' => $stats['processed']++,
                'skipped' => $stats['skipped']++,
                default => $stats['failed']++,
            };
        }

        return $stats;
    }

    /**
     * @return array{pin: string, punched_at: Carbon, status: int, verify_mode: int|null}|null
     */
    public function parseAttlogLine(string $line): ?array
    {
        $fields = explode("\t", $line);
        if (count($fields) < 2) {
            return null;
        }

        $pin = trim($fields[0]);
        $timestamp = trim($fields[1]);

        if ($pin === '' || $timestamp === '') {
            return null;
        }

        try {
            $punchedAt = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, config('app.timezone'));
        } catch (\Throwable) {
            try {
                $punchedAt = Carbon::parse($timestamp, config('app.timezone'));
            } catch (\Throwable) {
                return null;
            }
        }

        return [
            'pin' => $pin,
            'punched_at' => $punchedAt,
            'status' => isset($fields[2]) ? (int) $fields[2] : 0,
            'verify_mode' => isset($fields[3]) ? (int) $fields[3] : null,
        ];
    }

    private function processLog(FingerprintDevice $device, array $parsed, string $rawLine): string
    {
        $existing = FingerprintLog::query()
            ->where('fingerprint_device_id', $device->id)
            ->where('device_pin', $parsed['pin'])
            ->where('punched_at', $parsed['punched_at'])
            ->where('punch_status', $parsed['status'])
            ->first();

        if ($existing) {
            return 'skipped';
        }

        $employee = $this->findEmployeeByPin($device, $parsed['pin']);

        $log = FingerprintLog::query()->create([
            'fingerprint_device_id' => $device->id,
            'device_pin' => $parsed['pin'],
            'punched_at' => $parsed['punched_at'],
            'punch_status' => $parsed['status'],
            'verify_mode' => $parsed['verify_mode'],
            'raw_line' => $rawLine,
            'employee_id' => $employee?->id,
            'process_status' => 'pending',
        ]);

        if ($employee === null) {
            $log->update([
                'process_status' => 'failed',
                'process_message' => 'PIN tidak terdaftar di sistem.',
            ]);

            return 'failed';
        }

        if (! $this->attendanceMethods->fingerprintEnabled()) {
            $log->update([
                'process_status' => 'failed',
                'process_message' => 'Absensi fingerprint dinonaktifkan di pengaturan sistem.',
            ]);

            return 'failed';
        }

        if (! $device->branch_id) {
            $log->update([
                'process_status' => 'failed',
                'process_message' => 'Mesin belum dikaitkan ke cabang.',
            ]);

            return 'failed';
        }

        if ($employee->branch_id !== $device->branch_id) {
            $log->update([
                'process_status' => 'failed',
                'process_message' => 'Pegawai bukan dari cabang mesin ini.',
            ]);

            return 'failed';
        }

        try {
            $attendance = DB::transaction(function () use ($device, $employee, $parsed) {
                $evaluation = $this->shiftScheduleService->evaluatePunch(
                    $employee,
                    $parsed['punched_at'],
                );

                return Attendance::query()->create([
                    'employee_id' => $employee->id,
                    'branch_id' => $device->branch_id,
                    'fingerprint_device_id' => $device->id,
                    'type' => $evaluation['type'],
                    'source' => AttendanceSource::Fingerprint,
                    'attended_at' => $parsed['punched_at'],
                    'latitude' => 0,
                    'longitude' => 0,
                    'face_verified' => true,
                    'location_verified' => true,
                    'status' => $evaluation['status'],
                    'is_late' => $evaluation['is_late'],
                    'late_minutes' => $evaluation['late_minutes'],
                    'notes' => $evaluation['notes'].' · Mesin '.$device->serial_number,
                ]);
            });

            $log->update([
                'process_status' => 'processed',
                'attendance_id' => $attendance->id,
            ]);

            return 'processed';
        } catch (\Throwable $e) {
            Log::error('Fingerprint attendance failed', [
                'device' => $device->serial_number,
                'pin' => $parsed['pin'],
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'process_status' => 'failed',
                'process_message' => $e->getMessage(),
            ]);

            return 'failed';
        }
    }

    public function findEmployeeByPin(FingerprintDevice $device, string $pin): ?Employee
    {
        if ($device->branch_id) {
            $employee = Employee::query()
                ->where('branch_id', $device->branch_id)
                ->where('fingerprint_pin', $pin)
                ->where('is_active', true)
                ->first();

            if ($employee) {
                return $employee;
            }
        }

        return Employee::query()
            ->where('fingerprint_pin', $pin)
            ->where('is_active', true)
            ->first();
    }

    public function mapPunchStatusToType(int $status): AttendanceType
    {
        return match ($status) {
            1, 3, 5 => AttendanceType::CheckOut,
            default => AttendanceType::CheckIn,
        };
    }

    public function queueSyncEmployee(FingerprintDevice $device, Employee $employee): void
    {
        if (! $employee->fingerprint_pin) {
            return;
        }

        $employee->loadMissing('shift');

        $name = str_replace(["\t", "\n", "\r"], ' ', $employee->name);
        $tz = $employee->shift
            ? $this->shiftScheduleService->buildUserTzString($employee->shift)
            : '0000000100000000';

        $command = sprintf(
            'DATA UPDATE USERINFO PIN=%s\tName=%s\tPri=0\tPasswd=\tCard=\tGrp=1\tTZ=%s\tVerify=0',
            $employee->fingerprint_pin,
            $name,
            $tz,
        );

        $device->commands()->create([
            'command' => $command,
            'status' => 'pending',
        ]);
    }

    public function queueSyncShifts(FingerprintDevice $device): int
    {
        return $this->shiftScheduleService->queueSyncShiftsForDevice($device);
    }

    public function queueFullSync(FingerprintDevice $device, iterable $employees): void
    {
        $this->queueSyncShifts($device);

        foreach ($employees as $employee) {
            $this->queueSyncEmployee($device, $employee);
        }
    }

    public function queueSyncEmployeeToBranchDevices(Employee $employee): void
    {
        if (! $employee->fingerprint_pin || ! $employee->branch_id) {
            return;
        }

        $devices = FingerprintDevice::query()
            ->where('branch_id', $employee->branch_id)
            ->where('is_active', true)
            ->get();

        foreach ($devices as $device) {
            $this->queueSyncEmployee($device, $employee);
        }
    }
}
