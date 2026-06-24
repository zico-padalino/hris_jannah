<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Face\EnrollFaceRequest;
use App\Models\Employee;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaceEnrollmentController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    public function store(EnrollFaceRequest $request, Employee $employee): JsonResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $face = $this->attendanceService->enrollFace(
            employee: $employee,
            faceDescriptor: $request->validated('face_descriptor'),
            photo: $request->file('photo'),
            isPrimary: $request->boolean('is_primary', true),
        );

        return response()->json([
            'message' => 'Wajah pegawai berhasil didaftarkan.',
            'data' => $face,
        ], 201);
    }

    public function index(Request $request, Employee $employee): JsonResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        return response()->json([
            'data' => $employee->faces()->latest()->get(),
        ]);
    }

    private function authorizeBranchAccess(Request $request, int $branchId): void
    {
        if (! $request->user()->canManageBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }
    }
}
