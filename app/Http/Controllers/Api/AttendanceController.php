<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Models\Employee;
use App\Services\AttendanceMethodSettingsService;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceMethodSettingsService $attendanceMethods,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = \App\Models\Attendance::query()
            ->with(['employee', 'branch', 'branchLocation'])
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->integer('employee_id')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('attended_at', $request->string('date')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when(! $user->isSuperAdmin() && ! $user->isHr(), function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            })
            ->latest('attended_at');

        return response()->json($query->paginate(20));
    }

    public function scan(StoreAttendanceRequest $request): JsonResponse
    {
        $user = $request->user();
        $branchId = $request->integer('branch_id') ?: $user->branch_id;

        if ($branchId && ! $user->canManageBranch($branchId) && $user->employee?->branch_id !== $branchId) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }

        if (! $this->attendanceMethods->photoEnabled()) {
            return response()->json(['message' => 'Absensi foto/wajah dinonaktifkan.'], 422);
        }

        $result = $this->attendanceService->recordByFaceScan(
            latitude: (float) $request->validated('latitude'),
            longitude: (float) $request->validated('longitude'),
            faceDescriptor: $request->validated('face_descriptor'),
            branchId: $branchId,
            photo: $request->file('photo'),
        );

        $statusCode = $result['success'] ? 201 : 422;

        return response()->json($result, $statusCode);
    }

    public function scanSelf(Request $request): JsonResponse
    {
        if ($request->has('face_descriptor') && is_string($request->input('face_descriptor'))) {
            $decoded = json_decode($request->input('face_descriptor'), true);
            if (is_array($decoded)) {
                $request->merge(['face_descriptor' => $decoded]);
            }
        }

        $data = $request->validate([
            'method' => ['nullable', 'in:photo,gps'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'face_descriptor' => ['required_if:method,photo', 'nullable', 'array', 'min:64'],
            'face_descriptor.*' => ['numeric'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $method = $data['method'] ?? 'photo';
        $employee = $request->user()->employee;

        if ($employee === null) {
            return response()->json(['message' => 'Akun ini bukan pegawai.'], 403);
        }

        if ($method === 'gps') {
            if (! $this->attendanceMethods->gpsEnabled()) {
                return response()->json(['message' => 'Absensi GPS dinonaktifkan.'], 422);
            }

            $result = $this->attendanceService->recordByGpsForEmployee(
                $employee,
                (float) $data['latitude'],
                (float) $data['longitude'],
            );

            $statusCode = $result['success'] ? 201 : 422;

            return response()->json($result, $statusCode);
        }

        if (! $this->attendanceMethods->photoEnabled()) {
            return response()->json(['message' => 'Absensi foto/wajah dinonaktifkan.'], 422);
        }

        if (! $employee->faces()->exists()) {
            return response()->json(['message' => 'Wajah belum didaftarkan. Hubungi HRD.'], 422);
        }

        try {
            $attendance = $this->attendanceService->record(
                employee: $employee,
                latitude: (float) $data['latitude'],
                longitude: (float) $data['longitude'],
                faceDescriptor: $data['face_descriptor'],
                photo: $request->file('photo'),
            );
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $success = $attendance->status->isSuccessful();
        $message = $attendance->type->label();
        if ($attendance->is_late) {
            $message .= " · Terlambat {$attendance->late_minutes} menit";
        } elseif (! $success) {
            $message .= ' · '.$attendance->status->label();
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'attendance' => $attendance->load(['branch', 'branchLocation']),
        ], $success ? 201 : 422);
    }

    public function myHistory(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if ($employee === null) {
            return response()->json([
                'message' => 'Data pegawai tidak ditemukan untuk akun ini.',
            ], 404);
        }

        $attendances = $employee->attendances()
            ->with(['branch', 'branchLocation'])
            ->latest('attended_at')
            ->paginate(20);

        return response()->json($attendances);
    }

    public function employeeHistory(Request $request, Employee $employee): JsonResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $attendances = $employee->attendances()
            ->with(['branch', 'branchLocation'])
            ->latest('attended_at')
            ->paginate(20);

        return response()->json($attendances);
    }

    private function authorizeBranchAccess(Request $request, int $branchId): void
    {
        if (! $request->user()->canManageBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }
    }
}
