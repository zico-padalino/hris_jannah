<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request, Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($request, $branch->id);

        return response()->json([
            'data' => $branch->departments()->latest()->get(),
        ]);
    }

    public function store(StoreDepartmentRequest $request, Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $department = $branch->departments()->create($request->validated());

        return response()->json([
            'message' => 'Departemen berhasil ditambahkan.',
            'data' => $department,
        ], 201);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $this->authorizeBranchAccess($request, $department->branch_id);

        $department->update($request->validated());

        return response()->json([
            'message' => 'Departemen berhasil diperbarui.',
            'data' => $department->fresh(),
        ]);
    }

    public function destroy(Request $request, Department $department): JsonResponse
    {
        $this->authorizeBranchAccess($request, $department->branch_id);

        $department->delete();

        return response()->json([
            'message' => 'Departemen berhasil dihapus.',
        ]);
    }

    private function authorizeBranchAccess(Request $request, int $branchId): void
    {
        if (! $request->user()->canManageBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }
    }
}
