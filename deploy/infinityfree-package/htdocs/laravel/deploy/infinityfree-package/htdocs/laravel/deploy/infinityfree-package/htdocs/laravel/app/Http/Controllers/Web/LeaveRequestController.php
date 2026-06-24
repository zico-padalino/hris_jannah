<?php

namespace App\Http\Controllers\Web;

use App\Enums\LeaveStatus;
use App\Enums\Permission;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends WebController
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            abort(403);
        }

        $employee = $user->employee;

        if ($employee === null) {
            if ($user->hasPermission(Permission::LeaveApprove)) {
                return redirect()->route('leave-approvals.index');
            }

            abort(403, 'Akun Anda belum terhubung dengan data pegawai. Hubungi admin.');
        }

        $leaves = LeaveRequest::query()
            ->with(['employee', 'branch', 'approver'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(15);

        return view('leaves.index', compact('leaves'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorizePermission($request, Permission::LeaveRequest);

        if ($request->user()->employee === null) {
            if ($request->user()->hasPermission(Permission::LeaveApprove)) {
                return redirect()->route('leave-approvals.index');
            }

            return redirect()->route('leaves.index')
                ->with('error', 'Akun Anda belum terhubung dengan data pegawai. Hubungi admin.');
        }

        return view('leaves.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::LeaveRequest);

        $employee = $request->user()->employee;

        if ($employee === null) {
            if ($request->user()->hasPermission(Permission::LeaveApprove)) {
                return redirect()->route('leave-approvals.index');
            }

            return back()->with('error', 'Akun Anda belum terhubung dengan data pegawai. Hubungi admin.');
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

        LeaveRequest::query()->create([
            ...collect($data)->except('proof')->all(),
            'proof_path' => $proofPath,
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'status' => LeaveStatus::Pending,
        ]);

        return redirect()->route('leaves.index')->with('success', __('messages.leave_submitted'));
    }
}
