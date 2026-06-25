<?php

namespace App\Http\Controllers\Web;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceType;
use App\Enums\Permission;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Services\AttendanceDayGroupService;
use App\Services\ShiftScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceManageController extends WebController
{
    public function __construct(
        private readonly ShiftScheduleService $shiftScheduleService,
        private readonly AttendanceDayGroupService $attendanceDayGroupService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::AttendanceManage);
        $branchIds = $this->manageableBranchIds($request);

        $attendances = $this->attendanceDayGroupService
            ->paginateForRequest($request, $branchIds)
            ->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('attendances.manage', compact('attendances', 'branches'));
    }

    public function create(Request $request): View
    {
        $this->authorizePermission($request, Permission::AttendanceManage);
        $branchIds = $this->manageableBranchIds($request);

        $employees = Employee::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->where('is_active', true)->orderBy('name')->get();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)->orderBy('name')->get();

        return view('attendances.create', compact('employees', 'branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::AttendanceManage);

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'branch_id' => ['required', 'exists:branches,id'],
            'type' => ['required', 'in:check_in,check_out'],
            'attended_at' => ['required', 'date'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'status' => ['required', 'in:valid,invalid_face,invalid_location,invalid_both'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = Employee::query()->findOrFail($data['employee_id']);
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $attendedAt = \Illuminate\Support\Carbon::parse($data['attended_at'], config('app.timezone'));
        $type = AttendanceType::from($data['type']);
        $verificationStatus = AttendanceStatus::from($data['status']);
        $schedule = $this->shiftScheduleService->evaluateAttendanceRecord(
            $employee,
            $attendedAt,
            $type,
            $verificationStatus,
        );

        $notes = collect([$data['notes'] ?? null, $schedule['note']])->filter()->implode('. ');

        Attendance::query()->create([
            'employee_id' => $data['employee_id'],
            'branch_id' => $data['branch_id'],
            'type' => $type,
            'source' => AttendanceSource::Manual,
            'attended_at' => $attendedAt,
            'latitude' => $data['latitude'] ?? 0,
            'longitude' => $data['longitude'] ?? 0,
            'status' => $schedule['status'],
            'is_late' => $schedule['is_late'],
            'late_minutes' => $schedule['late_minutes'],
            'notes' => $notes !== '' ? $notes : null,
            'face_verified' => in_array($data['status'], ['valid', 'invalid_location'], true),
            'location_verified' => in_array($data['status'], ['valid', 'invalid_face'], true),
        ]);

        return redirect()->route('attendances.manage')->with('success', 'Absensi manual berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorizePermission($request, Permission::AttendanceManage);
        $this->authorizeBranchAccess($request, $attendance->branch_id);

        $data = $request->validate([
            'status' => ['required', 'in:valid,late,invalid_face,invalid_location,invalid_both'],
            'notes' => ['nullable', 'string'],
        ]);

        $attendance->update([
            'status' => AttendanceStatus::from($data['status']),
            'notes' => $data['notes'] ?? null,
            'is_late' => $data['status'] === 'late',
            'late_minutes' => $data['status'] === 'late' ? $attendance->late_minutes : null,
            'face_verified' => in_array($data['status'], ['valid', 'invalid_location', 'late'], true),
            'location_verified' => in_array($data['status'], ['valid', 'invalid_face', 'late'], true),
        ]);

        return back()->with('success', 'Status absensi diperbarui.');
    }
}
