<?php

namespace App\Http\Controllers\Web;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Permission;
use App\Models\LeaveRequest;
use App\Services\LeaveBadgeService;
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

        $category = $this->resolveCategory($request);

        if ($request->filled('ack')) {
            $badgeService = app(LeaveBadgeService::class);

            if ($request->string('ack')->toString() === 'all') {
                $badgeService->markAllOwnStatusesRead($user);
            } else {
                $badgeService->markOwnStatusRead($user, $request->integer('ack'));
            }

            return redirect()->route('leaves.index', ['category' => $category]);
        }

        $categoryTypes = LeaveType::forApprovalCategory($category);

        $leaves = LeaveRequest::query()
            ->with(['employee', 'branch', 'approver'])
            ->where('employee_id', $employee->id)
            ->when($categoryTypes !== [], fn ($q) => $q->whereIn('type', $categoryTypes))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view("leaves.{$category}.index", compact('leaves', 'category'));
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

        $category = $this->resolveCategory($request);

        return view("leaves.{$category}.create", compact('category'));
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

        $category = $this->resolveCategory($request);
        $allowedTypes = array_map(
            fn (LeaveType $type) => $type->value,
            LeaveType::forApprovalCategory($category)
        );

        $data = $request->validate([
            'type' => ['required', 'in:'.implode(',', $allowedTypes)],
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

        return redirect()
            ->route('leaves.index', ['category' => $category])
            ->with('success', __('messages.leave_submitted'));
    }

    /** @return 'cuti'|'izin'|'lembur' */
    private function resolveCategory(Request $request): string
    {
        $category = $request->string('category', 'cuti')->toString();

        return in_array($category, ['cuti', 'izin', 'lembur'], true) ? $category : 'cuti';
    }
}
