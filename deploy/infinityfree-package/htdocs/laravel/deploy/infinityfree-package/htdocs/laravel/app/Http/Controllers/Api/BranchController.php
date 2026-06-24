<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $branches = Branch::query()
            ->withCount(['employees', 'locations'])
            ->when(! $user->isSuperAdmin() && ! $user->isHr(), function ($query) use ($user) {
                $query->where('id', $user->branch_id);
            })
            ->latest()
            ->paginate(15);

        return response()->json($branches);
    }

    public function store(StoreBranchRequest $request): JsonResponse
    {
        $branch = Branch::query()->create($request->validated());

        return response()->json([
            'message' => 'Cabang berhasil ditambahkan.',
            'data' => $branch,
        ], 201);
    }

    public function show(Branch $branch): JsonResponse
    {
        $branch->load(['locations', 'departments']);

        return response()->json(['data' => $branch]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): JsonResponse
    {
        $branch->update($request->validated());

        return response()->json([
            'message' => 'Cabang berhasil diperbarui.',
            'data' => $branch->fresh(),
        ]);
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();

        return response()->json([
            'message' => 'Cabang berhasil dihapus.',
        ]);
    }
}
