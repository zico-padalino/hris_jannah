<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchLocation\StoreBranchLocationRequest;
use App\Http\Requests\BranchLocation\UpdateBranchLocationRequest;
use App\Models\Branch;
use App\Models\BranchLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchLocationController extends Controller
{
    public function index(Request $request, Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $locations = $branch->locations()->latest()->get();

        return response()->json(['data' => $locations]);
    }

    public function store(StoreBranchLocationRequest $request, Branch $branch): JsonResponse
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $location = $branch->locations()->create($request->validated());

        return response()->json([
            'message' => 'Lokasi absensi berhasil ditambahkan.',
            'data' => $location,
        ], 201);
    }

    public function update(UpdateBranchLocationRequest $request, BranchLocation $branchLocation): JsonResponse
    {
        $this->authorizeBranchAccess($request, $branchLocation->branch_id);

        $branchLocation->update($request->validated());

        return response()->json([
            'message' => 'Lokasi absensi berhasil diperbarui.',
            'data' => $branchLocation->fresh(),
        ]);
    }

    public function destroy(Request $request, BranchLocation $branchLocation): JsonResponse
    {
        $this->authorizeBranchAccess($request, $branchLocation->branch_id);

        $branchLocation->delete();

        return response()->json([
            'message' => 'Lokasi absensi berhasil dihapus.',
        ]);
    }

    private function authorizeBranchAccess(Request $request, int $branchId): void
    {
        if (! $request->user()->canManageBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }
    }
}
