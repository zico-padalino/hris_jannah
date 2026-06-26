<?php

namespace App\Services;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeFace;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttendanceService
{
    public function __construct(
        private readonly FaceRecognitionService $faceRecognitionService,
        private readonly GeofenceService $geofenceService,
        private readonly ShiftScheduleService $shiftScheduleService,
    ) {}

    public function record(
        Employee $employee,
        float $latitude,
        float $longitude,
        array $faceDescriptor,
        ?UploadedFile $photo = null,
    ): Attendance {
        $this->ensureEmployeeCanRecordAttendance($employee);

        return DB::transaction(function () use ($employee, $latitude, $longitude, $faceDescriptor, $photo) {
            $branch = Branch::query()->findOrFail($employee->branch_id);
            $locations = $this->geofenceService->getActiveLocationsForBranch($branch->id);
            $locationMatch = $this->geofenceService->findMatchingLocation($latitude, $longitude, $locations);

            $faceMatch = $this->faceRecognitionService->matchEmployee($employee->faces, $faceDescriptor);

            $faceVerified = $faceMatch['matched'];
            $locationVerified = $locationMatch !== null;

            $status = match (true) {
                $faceVerified && $locationVerified => AttendanceStatus::Valid,
                ! $faceVerified && ! $locationVerified => AttendanceStatus::InvalidBoth,
                ! $faceVerified => AttendanceStatus::InvalidFace,
                default => AttendanceStatus::InvalidLocation,
            };

            $attendedAt = now();
            $type = $this->shiftScheduleService->resolveAlternatingPunchType($employee, $attendedAt);
            $schedule = $this->shiftScheduleService->evaluateAttendanceRecord(
                $employee,
                $attendedAt,
                $type,
                $status,
            );

            $notes = $schedule['note'];

            $photoPath = null;
            if ($photo !== null) {
                $photoPath = $photo->store("attendances/{$employee->id}", 'public');
            }

            return Attendance::query()->create([
                'employee_id' => $employee->id,
                'branch_id' => $branch->id,
                'branch_location_id' => $locationMatch['location']->id ?? null,
                'type' => $type,
                'source' => AttendanceSource::Face,
                'attended_at' => $attendedAt,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'photo_path' => $photoPath,
                'face_match_score' => $faceMatch['score'],
                'face_verified' => $faceVerified,
                'location_verified' => $locationVerified,
                'distance_meters' => $locationMatch['distance_meters'] ?? null,
                'status' => $schedule['status'],
                'is_late' => $schedule['is_late'],
                'late_minutes' => $schedule['late_minutes'],
                'notes' => $notes,
            ]);
        });
    }

    public function recordByFaceScan(
        float $latitude,
        float $longitude,
        array $faceDescriptor,
        ?int $branchId = null,
        ?UploadedFile $photo = null,
    ): array {
        $match = $this->faceRecognitionService->findEmployeeByFace($faceDescriptor, $branchId);

        if ($match === null) {
            return [
                'success' => false,
                'message' => 'Wajah tidak dikenali.',
            ];
        }

        /** @var Employee $employee */
        $employee = $match['employee'];

        try {
            $attendance = $this->record($employee, $latitude, $longitude, $faceDescriptor, $photo);
        } catch (\DomainException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $message = $attendance->type->label();
        if ($attendance->is_late) {
            $message .= " · Terlambat {$attendance->late_minutes} menit";
        } elseif (! $attendance->status->isSuccessful()) {
            $message .= ' · '.$attendance->status->label();
        }

        return [
            'success' => $attendance->status->isSuccessful(),
            'message' => $message,
            'attendance' => $attendance->load(['employee', 'branch', 'branchLocation']),
            'face_match_score' => $match['score'],
        ];
    }

    public function recordByGps(Employee $employee, float $latitude, float $longitude): Attendance
    {
        $this->ensureEmployeeCanRecordAttendance($employee);

        return DB::transaction(function () use ($employee, $latitude, $longitude) {
            $branch = Branch::query()->findOrFail($employee->branch_id);
            $locations = $this->geofenceService->getActiveLocationsForBranch($branch->id);
            $locationMatch = $this->geofenceService->findMatchingLocation($latitude, $longitude, $locations);

            $locationVerified = $locationMatch !== null;
            $status = $locationVerified ? AttendanceStatus::Valid : AttendanceStatus::InvalidLocation;

            $attendedAt = now();
            $type = $this->shiftScheduleService->resolveAlternatingPunchType($employee, $attendedAt);
            $schedule = $this->shiftScheduleService->evaluateAttendanceRecord(
                $employee,
                $attendedAt,
                $type,
                $status,
            );

            return Attendance::query()->create([
                'employee_id' => $employee->id,
                'branch_id' => $branch->id,
                'branch_location_id' => $locationMatch['location']->id ?? null,
                'type' => $type,
                'source' => AttendanceSource::Gps,
                'attended_at' => $attendedAt,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'photo_path' => null,
                'face_match_score' => null,
                'face_verified' => false,
                'location_verified' => $locationVerified,
                'distance_meters' => $locationMatch['distance_meters'] ?? null,
                'status' => $schedule['status'],
                'is_late' => $schedule['is_late'],
                'late_minutes' => $schedule['late_minutes'],
                'notes' => trim(($schedule['note'] ?? '').' · Absensi GPS (tanpa verifikasi wajah)'),
            ]);
        });
    }

    /** @return array{success: bool, message: string, attendance?: Attendance} */
    public function recordByGpsForEmployee(Employee $employee, float $latitude, float $longitude): array
    {
        try {
            $attendance = $this->recordByGps($employee, $latitude, $longitude);
        } catch (\DomainException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        $message = $attendance->type->label();
        if ($attendance->is_late) {
            $message .= " · Terlambat {$attendance->late_minutes} menit";
        } elseif (! $attendance->status->isSuccessful()) {
            $message .= ' · '.$attendance->status->label();
        }

        return [
            'success' => $attendance->status->isSuccessful(),
            'message' => $message,
            'attendance' => $attendance->load(['employee', 'branch', 'branchLocation']),
        ];
    }

    public function enrollFace(Employee $employee, array $faceDescriptor, UploadedFile $photo, bool $isPrimary = true): \App\Models\EmployeeFace
    {
        if ($isPrimary) {
            $employee->faces()->update(['is_primary' => false]);
        }

        $photoPath = $photo->store("faces/{$employee->id}", 'public');

        return $employee->faces()->create([
            'photo_path' => $photoPath,
            'face_descriptor' => $faceDescriptor,
            'is_primary' => $isPrimary,
            'enrolled_at' => now(),
        ]);
    }

    private function ensureEmployeeCanRecordAttendance(Employee $employee): void
    {
        if (! $employee->canRecordAttendance()) {
            throw new \DomainException(__('attendance.no_shift_assigned'));
        }
    }

    public function deleteFace(EmployeeFace $face): void
    {
        DB::transaction(function () use ($face) {
            $employee = Employee::query()->findOrFail($face->employee_id);
            $wasPrimary = $face->is_primary;

            if (filled($face->photo_path)) {
                Storage::disk('public')->delete($face->photo_path);
            }

            $face->delete();

            if ($wasPrimary) {
                $nextPrimary = $employee->faces()->orderByDesc('enrolled_at')->first();

                if ($nextPrimary !== null) {
                    $nextPrimary->update(['is_primary' => true]);
                }
            }
        });
    }
}
