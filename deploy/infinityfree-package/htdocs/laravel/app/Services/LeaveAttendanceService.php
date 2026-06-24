<?php

namespace App\Services;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceType;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class LeaveAttendanceService
{
    public function syncApprovedLeave(LeaveRequest $leave): int
    {
        $leave->loadMissing('employee');

        $created = 0;
        $period = CarbonPeriod::create(
            $leave->start_date->toDateString(),
            $leave->end_date->toDateString()
        );

        foreach ($period as $date) {
            $attendedAt = Carbon::parse($date)->startOfDay()->setTime(8, 0);

            $attendance = Attendance::query()->firstOrCreate(
                [
                    'leave_request_id' => $leave->id,
                    'attended_at' => $attendedAt,
                ],
                [
                    'employee_id' => $leave->employee_id,
                    'branch_id' => $leave->branch_id,
                    'type' => AttendanceType::Leave,
                    'source' => AttendanceSource::Leave,
                    'latitude' => 0,
                    'longitude' => 0,
                    'face_verified' => true,
                    'location_verified' => true,
                    'status' => AttendanceStatus::Valid,
                    'notes' => $this->buildNote($leave),
                ]
            );

            if ($attendance->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }

    public function removeForLeave(LeaveRequest $leave): void
    {
        Attendance::query()
            ->where('leave_request_id', $leave->id)
            ->delete();
    }

    private function buildNote(LeaveRequest $leave): string
    {
        $note = $leave->type->label().' disetujui';

        if ($leave->admin_notes) {
            $note .= ' — '.$leave->admin_notes;
        }

        return $note;
    }
}
