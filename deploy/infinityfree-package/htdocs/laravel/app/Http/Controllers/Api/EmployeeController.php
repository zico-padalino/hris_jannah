<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $employees = Employee::query()
            ->with(['branch', 'department', 'faces'])
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->integer('department_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(! $user->isSuperAdmin() && ! $user->isHr(), function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->latest()
            ->paginate(15);

        return response()->json($employees);
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->authorizeBranchAccess($request, (int) $data['branch_id']);

        $employee = Employee::query()->create($data);

        return response()->json([
            'message' => 'Pegawai berhasil ditambahkan.',
            'data' => $employee->load(['branch', 'department']),
        ], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json([
            'data' => $employee->load(['branch', 'department', 'faces', 'user']),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $data = $request->validated();
        $branchId = (int) ($data['branch_id'] ?? $employee->branch_id);
        $this->authorizeBranchAccess($request, $branchId);

        $employee->update($data);

        return response()->json([
            'message' => 'Pegawai berhasil diperbarui.',
            'data' => $employee->fresh()->load(['branch', 'department']),
        ]);
    }

    public function destroy(Request $request, Employee $employee): JsonResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $employee->delete();

        return response()->json([
            'message' => 'Pegawai berhasil dihapus.',
        ]);
    }

    public function byBranch(Request $request, Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $employees = $branch->employees()
            ->with(['department', 'faces'])
            ->latest()
            ->paginate(15);

        return response()->json($employees);
    }

    private function authorizeBranchAccess(Request $request, int $branchId): void
    {
        if (! $request->user()->canManageBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }
    }
}
