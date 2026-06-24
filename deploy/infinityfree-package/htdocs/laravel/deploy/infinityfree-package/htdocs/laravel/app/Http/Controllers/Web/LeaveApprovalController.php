<?php

namespace App\Http\Controllers\Web;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Permission;
use App\Models\LeaveRequest;
use App\Services\LeaveAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaveApprovalController extends WebController
{
    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::LeaveApprove);
        $branchIds = $this->manageableBranchIds($request);
        $status = $request->string('status', 'pending')->toString();
        $category = $request->string('category')->toString();

        $baseQuery = LeaveRequest::query()
            ->with(['employee', 'branch', 'approver'])
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds));

        $pendingCount = (clone $baseQuery)->where('status', LeaveStatus::Pending)->count();
        $pendingBreakdown = [
            'cuti' => (clone $baseQuery)->where('status', LeaveStatus::Pending)->whereIn('type', [LeaveType::Annual, LeaveType::Sick])->count(),
            'izin' => (clone $baseQuery)->where('status', LeaveStatus::Pending)->where('type', LeaveType::Permission)->count(),
            'lembur' => (clone $baseQuery)->where('status', LeaveStatus::Pending)->where('type', LeaveType::Overtime)->count(),
        ];

        $categoryTypes = $category !== '' ? LeaveType::forApprovalCategory($category) : [];

        $leaves = (clone $baseQuery)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($categoryTypes !== [], fn ($q) => $q->whereIn('type', $categoryTypes))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('leave-approvals.index', compact('leaves', 'status', 'pendingCount', 'pendingBreakdown', 'category'));
    }

    public function approve(Request $request, LeaveRequest $leave, LeaveAttendanceService $leaveAttendanceService): RedirectResponse
    {
        $this->authorizePermission($request, Permission::LeaveApprove);
        $this->authorizeBranchAccess($request, $leave->branch_id);
        $this->ensurePending($leave);

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($leave, $request, $data, $leaveAttendanceService) {
            $leave->update([
                'status' => LeaveStatus::Approved,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'admin_notes' => $data['admin_notes'] ?? null,
            ]);

            if ($leave->type !== LeaveType::Overtime) {
                $leaveAttendanceService->syncApprovedLeave($leave->fresh());
            }
        });

        return back()->with('success', $leave->type === LeaveType::Overtime
            ? __('messages.leave_approved_overtime')
            : __('messages.leave_approved'));
    }

    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorizePermission($request, Permission::LeaveApprove);
        $this->authorizeBranchAccess($request, $leave->branch_id);
        $this->ensurePending($leave);

        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $leave->update([
            'status' => LeaveStatus::Rejected,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return back()->with('success', __('messages.leave_rejected'));
    }

    private function ensurePending(LeaveRequest $leave): void
    {
        if ($leave->status !== LeaveStatus::Pending) {
            abort(422, 'Pengajuan ini sudah diproses sebelumnya.');
        }
    }
}
