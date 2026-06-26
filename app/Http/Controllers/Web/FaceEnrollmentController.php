<?php

namespace App\Http\Controllers\Web;

use App\Models\Employee;
use App\Models\EmployeeFace;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaceEnrollmentController extends WebController
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    public function create(Request $request, Employee $employee): View
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $employee->load(['branch', 'faces']);

        return view('faces.enroll', compact('employee'));
    }

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:5120'],
            'face_descriptor' => ['required', 'json'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $descriptor = json_decode($data['face_descriptor'], true);

        if (! is_array($descriptor) || count($descriptor) < 64) {
            return back()->with('error', 'Data wajah tidak valid. Silakan scan ulang.');
        }

        $this->attendanceService->enrollFace(
            employee: $employee,
            faceDescriptor: $descriptor,
            photo: $request->file('photo'),
            isPrimary: $request->boolean('is_primary', true),
        );

        return redirect()->route('employees.show', $employee)->with('success', 'Wajah pegawai berhasil didaftarkan.');
    }

    public function destroy(Request $request, Employee $employee, EmployeeFace $face): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        if ($face->employee_id !== $employee->id) {
            abort(404);
        }

        $this->attendanceService->deleteFace($face);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Data wajah berhasil dihapus.');
    }
}
