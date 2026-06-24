<?php

namespace App\Http\Controllers\Api;

use App\Enums\LeaveStatus;
use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = LeaveRequest::query()->with(['employee', 'branch']);

        if ($user->employee && ! $user->canManageBranch($user->branch_id)) {
            $query->where('employee_id', $user->employee->id);
        } elseif (! $user->isSuperAdmin() && ! $user->isHr()) {
            $query->where('branch_id', $user->branch_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;

        if ($employee === null) {
            return response()->json(['message' => 'Akun ini bukan pegawai.'], 403);
        }

        $data = $request->validate([
            'type' => ['required', 'in:annual,sick,permission,overtime'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'proof' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
                'required_unless:type,annual,overtime',
            ],
        ]);

        $proofPath = null;

        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store("leaves/{$employee->id}", 'public');
        }

        $leave = LeaveRequest::query()->create([
            ...collect($data)->except('proof')->all(),
            'proof_path' => $proofPath,
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'status' => LeaveStatus::Pending,
        ]);

        return response()->json([
            'message' => 'Pengajuan cuti/izin berhasil dikirim.',
            'data' => $leave->load('employee'),
        ], 201);
    }
}
