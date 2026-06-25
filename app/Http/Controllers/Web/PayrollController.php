<?php

namespace App\Http\Controllers\Web;

use App\Enums\PayrollStatus;
use App\Enums\Permission;
use App\Enums\AttendanceStatus;
use App\Models\Branch;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Services\PayrollDeductionConfig;
use App\Services\PayrollService;
use App\Services\PayrollSlipConfig;
use App\Services\PayrollSlipService;
use App\Services\PayrollSlipSignatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollController extends WebController
{
    public function __construct(
        private readonly PayrollService $payrollService,
        private readonly PayrollDeductionConfig $deductionConfig,
        private readonly PayrollSlipService $slipService,
        private readonly PayrollSlipConfig $slipConfig,
        private readonly PayrollSlipSignatureService $signatureService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $branchIds = $this->manageableBranchIds($request);

        if ($user->hasPermission(Permission::PayrollManage)) {
            $periods = PayrollPeriod::query()
                ->with('branch')
                ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
                ->latest('year')->latest('month')
                ->paginate(15);

            $branches = Branch::query()
                ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
                ->where('is_active', true)->orderBy('name')->get();

            return view('payrolls.index', compact('periods', 'branches'));
        }

        $employee = $user->employee;
        abort_unless($employee, 403);

        $items = $employee->payrollItems()
            ->with('payrollPeriod.branch')
            ->latest('id')
            ->paginate(15);

        return view('payrolls.my', compact('items'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::PayrollManage);

        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        if ($data['branch_id']) {
            $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        }

        $period = PayrollPeriod::query()->create([
            'branch_id' => $data['branch_id'],
            'name' => 'Payroll '.str_pad($data['month'], 2, '0', STR_PAD_LEFT).'/'.$data['year'],
            'month' => $data['month'],
            'year' => $data['year'],
            'status' => PayrollStatus::Draft,
        ]);

        $this->payrollService->generate($period);

        return redirect()->route('payrolls.show', $period)->with('success', 'Payroll berhasil dibuat.');
    }

    public function show(Request $request, PayrollPeriod $payroll): View
    {
        if ($payroll->branch_id) {
            $this->authorizeBranchAccess($request, $payroll->branch_id);
        }

        $this->authorizePermission($request, Permission::PayrollManage);

        $payroll->load(['items.employee', 'items.slipSignature', 'branch']);

        return view('payrolls.show', compact('payroll'));
    }

    public function regenerate(Request $request, PayrollPeriod $payroll): RedirectResponse
    {
        if ($payroll->branch_id) {
            $this->authorizeBranchAccess($request, $payroll->branch_id);
        }

        $this->authorizePermission($request, Permission::PayrollManage);

        if ($payroll->status === PayrollStatus::Finalized) {
            return back()->with('error', 'Payroll final tidak dapat di-generate ulang.');
        }

        $this->payrollService->generate($payroll);

        return back()->with('success', 'Payroll berhasil di-generate ulang.');
    }

    public function finalize(Request $request, PayrollPeriod $payroll): RedirectResponse
    {
        if ($payroll->branch_id) {
            $this->authorizeBranchAccess($request, $payroll->branch_id);
        }

        $this->authorizePermission($request, Permission::PayrollManage);

        $this->payrollService->finalize($payroll);

        return back()->with('success', 'Payroll difinalisasi.');
    }

    public function deductionDetails(Request $request, PayrollPeriod $payroll, PayrollItem $item): View
    {
        abort_unless($item->payroll_period_id === $payroll->id, 404);

        if ($payroll->branch_id) {
            $this->authorizeBranchAccess($request, $payroll->branch_id);
        }

        $user = $request->user();

        if ($user->hasPermission(Permission::PayrollManage)) {
            // authorized
        } elseif ($user->hasPermission(Permission::PayrollViewOwn)) {
            abort_unless($user->employee?->id === $item->employee_id, 403);
        } else {
            abort(403);
        }

        $item->load('employee.shift');
        $payroll->load('branch');

        $attendances = $this->payrollService->deductibleAttendances($item->employee, $payroll);
        $deductionPer = $this->deductionConfig->attendanceAmount();

        $lateCount = $attendances->where('status', AttendanceStatus::Late)->count();
        $invalidCount = $attendances->count() - $lateCount;

        $backUrl = $user->hasPermission(Permission::PayrollManage)
            ? route('payrolls.show', $payroll)
            : route('payrolls.index');

        return view('payrolls.deduction-details', compact(
            'payroll',
            'item',
            'attendances',
            'deductionPer',
            'lateCount',
            'invalidCount',
            'backUrl',
        ));
    }

    public function slip(Request $request, PayrollPeriod $payroll, PayrollItem $item): View
    {
        abort_unless($item->payroll_period_id === $payroll->id, 404);

        if ($payroll->branch_id) {
            $this->authorizeBranchAccess($request, $payroll->branch_id);
        }

        $user = $request->user();

        if ($user->hasPermission(Permission::PayrollManage)) {
            // authorized
        } elseif ($user->hasPermission(Permission::PayrollViewOwn)) {
            abort_unless($user->employee?->id === $item->employee_id, 403);
        } else {
            abort(403);
        }

        $slip = $this->slipService->build($item, $payroll);

        $backUrl = $user->hasPermission(Permission::PayrollManage)
            ? route('payrolls.show', $payroll)
            : route('payrolls.index');

        return view('payrolls.slip', [
            ...$slip,
            'backUrl' => $backUrl,
        ]);
    }

    public function signature(Request $request): StreamedResponse
    {
        $user = $request->user();

        if (! $user->hasPermission(Permission::PayrollManage) && ! $user->hasPermission(Permission::PayrollViewOwn)) {
            abort(403);
        }

        if (! $this->slipConfig->hasSignature()) {
            abort(404);
        }

        $path = $this->slipConfig->all()['signature_path'];

        return Storage::disk('public')->response(
            $path,
            basename($path),
            ['Content-Disposition' => 'inline'],
        );
    }

    public function verifySlip(string $code): View
    {
        $result = $this->slipService->verify($code);

        abort_unless($result !== null, 404);

        return view('payrolls.slip-verify', ['verification' => $result]);
    }

    public function requestSignature(Request $request, PayrollPeriod $payroll, PayrollItem $item): RedirectResponse
    {
        abort_unless($item->payroll_period_id === $payroll->id, 404);

        if ($payroll->branch_id) {
            $this->authorizeBranchAccess($request, $payroll->branch_id);
        }

        $user = $request->user();

        if ($user->hasPermission(Permission::PayrollManage)) {
            // authorized
        } elseif ($user->hasPermission(Permission::PayrollViewOwn)) {
            abort_unless($user->employee?->id === $item->employee_id, 403);
        } else {
            abort(403);
        }

        $this->signatureService->request($item, $user);

        return back()->with('success', __('pages.payroll_slip.signature_requested'));
    }

    public function approveSignature(Request $request, PayrollPeriod $payroll, PayrollItem $item): RedirectResponse
    {
        abort_unless($item->payroll_period_id === $payroll->id, 404);

        if ($payroll->branch_id) {
            $this->authorizeBranchAccess($request, $payroll->branch_id);
        }

        $this->authorizePermission($request, Permission::PayrollManage);

        $this->signatureService->approve($item, $request->user());

        return back()->with('success', __('pages.payroll_slip.signature_approved'));
    }
}
