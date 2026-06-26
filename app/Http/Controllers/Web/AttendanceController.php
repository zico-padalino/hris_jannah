<?php

namespace App\Http\Controllers\Web;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeFace;
use App\Models\SystemSetting;
use App\Services\AttendanceDayGroupService;
use App\Services\AttendanceMethodSettingsService;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends WebController
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceDayGroupService $attendanceDayGroupService,
        private readonly AttendanceMethodSettingsService $attendanceMethods,
    ) {}

    public function index(Request $request): View
    {
        $branchIds = $this->manageableBranchIds($request);

        $attendances = $this->attendanceDayGroupService
            ->paginateForRequest($request, $branchIds)
            ->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('attendances.index', compact('attendances', 'branches'));
    }

    public function scanForm(Request $request): View
    {
        $user = $request->user();
        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->when($user->employee && $user->role->value === 'employee', fn ($q) => $q->where('id', $user->branch_id))
            ->where('is_active', true)
            ->with('locations')
            ->orderBy('name')
            ->get();

        $branchesForJs = $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'locations' => $branch->locations->map(function ($location) {
                    return [
                        'name' => $location->name,
                        'latitude' => (float) $location->latitude,
                        'longitude' => (float) $location->longitude,
                        'radius_meters' => $location->radius_meters,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $defaultBranchId = $branches->first()?->id;

        $methods = $this->attendanceMethods->all();
        $isEmployeeAccount = $user->employee !== null && $user->role->value === 'employee';
        $employeesForGps = collect();

        if ($methods['gps'] && ! $isEmployeeAccount) {
            $employeesForGps = Employee::query()
                ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'employee_number', 'branch_id']);
        }

        $facesForJs = collect();
        $faceMatchThreshold = (float) SystemSetting::getValue(
            'face_match_threshold',
            config('attendance.face_match_threshold', 0.6),
        );

        if ($methods['photo'] ?? false) {
            $facesForJs = EmployeeFace::query()
                ->with(['employee:id,name,branch_id'])
                ->whereHas('employee', function ($query) use ($branchIds, $user, $isEmployeeAccount) {
                    $query->where('is_active', true);

                    if ($branchIds !== null) {
                        $query->whereIn('branch_id', $branchIds);
                    }

                    if ($isEmployeeAccount && $user->employee) {
                        $query->where('id', $user->employee->id);
                    }
                })
                ->get(['id', 'employee_id', 'face_descriptor'])
                ->map(fn (EmployeeFace $face) => [
                    'employee_id' => $face->employee_id,
                    'branch_id' => $face->employee->branch_id,
                    'employee_name' => $face->employee->name,
                    'descriptor' => $face->face_descriptor,
                ])
                ->values();
        }

        return view('attendances.scan', compact(
            'branches',
            'branchesForJs',
            'defaultBranchId',
            'methods',
            'isEmployeeAccount',
            'employeesForGps',
            'facesForJs',
            'faceMatchThreshold',
        ));
    }

    public function scan(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'method' => ['required', 'in:photo,gps'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'face_descriptor' => ['required_if:method,photo', 'nullable', 'json'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($data['method'] === 'photo' && ! $this->attendanceMethods->photoEnabled()) {
            return back()->with('error', 'Absensi foto/wajah dinonaktifkan di pengaturan sistem.');
        }

        if ($data['method'] === 'gps' && ! $this->attendanceMethods->gpsEnabled()) {
            return back()->with('error', 'Absensi GPS lokasi dinonaktifkan di pengaturan sistem.');
        }

        $branchId = $data['branch_id'] ?? $user->branch_id;

        if ($branchId && ! $user->canManageBranch((int) $branchId) && $user->employee?->branch_id !== (int) $branchId) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }

        if ($data['method'] === 'gps') {
            $employee = $user->employee;

            if ($employee === null) {
                if (empty($data['employee_id'])) {
                    return back()->with('error', 'Pilih pegawai untuk absensi GPS.');
                }

                $employee = Employee::query()->findOrFail($data['employee_id']);
                $this->authorizeBranchAccess($request, $employee->branch_id);
            }

            $result = $this->attendanceService->recordByGpsForEmployee(
                $employee,
                (float) $data['latitude'],
                (float) $data['longitude'],
            );
        } else {
            $descriptor = json_decode($data['face_descriptor'] ?? '', true);

            if (! is_array($descriptor) || count($descriptor) < 64) {
                return back()->with('error', 'Data wajah tidak valid. Silakan scan ulang.');
            }

            $result = $this->attendanceService->recordByFaceScan(
                latitude: (float) $data['latitude'],
                longitude: (float) $data['longitude'],
                faceDescriptor: $descriptor,
                branchId: $branchId ? (int) $branchId : null,
                photo: $request->file('photo'),
            );
        }

        if ($result['success']) {
            $flash = $result['attendance']->is_late ? 'warning' : 'success';

            return redirect()->route('attendances.index')->with($flash, 'Absensi tercatat: '.$result['message']);
        }

        $message = $result['message'] ?? 'Absensi gagal.';

        if (isset($result['attendance'])) {
            $message .= ' Status: '.$result['attendance']->status->label();
        }

        return back()->with('error', $message);
    }

    public function employeeHistory(Request $request, Employee $employee): View
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $attendances = $employee->attendances()
            ->with(['branch', 'branchLocation'])
            ->latest('attended_at')
            ->paginate(20);

        return view('attendances.employee', compact('employee', 'attendances'));
    }
}
