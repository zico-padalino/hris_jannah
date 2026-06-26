<?php

namespace App\Http\Controllers\Web;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Shift;
use App\Services\EmployeeUserSyncService;
use App\Services\FingerprintAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends WebController
{
    public function __construct(
        private readonly FingerprintAttendanceService $fingerprintAttendanceService,
        private readonly EmployeeUserSyncService $employeeUserSyncService,
    ) {}

    public function index(Request $request): View
    {
        $branchIds = $this->manageableBranchIds($request);

        $employees = Employee::query()
            ->with(['branch', 'department', 'position', 'faces', 'user'])
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('employees.index', compact('employees', 'branches'));
    }

    public function create(Request $request): View
    {
        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $shifts = $this->activeShifts($branchIds);

        $positions = Position::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('employees.create', compact('branches', 'departments', 'shifts', 'positions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedEmployeeData($request);

        $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['base_salary'] = $data['base_salary'] ?? 0;
        $data['fingerprint_pin'] = $data['fingerprint_pin'] ?: null;

        DB::transaction(function () use ($data) {
            $employee = Employee::query()->create($data);
            $this->employeeUserSyncService->syncFromEmployee($employee->fresh());
        });

        return redirect()->route('employees.index')->with('success', 'Pegawai dan akun pengguna berhasil ditambahkan.');
    }

    public function show(Request $request, Employee $employee): View
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $employee->load(['branch', 'department', 'position', 'shift', 'faces', 'user', 'attendances' => fn ($q) => $q->latest('attended_at')->limit(5)]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Request $request, Employee $employee): View
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $shifts = $this->activeShifts($branchIds);

        $positions = Position::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('employees.edit', compact('employee', 'branches', 'departments', 'shifts', 'positions'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $data = $this->validatedEmployeeData($request, $employee);

        $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        $data['is_active'] = $request->boolean('is_active', false);
        $data['fingerprint_pin'] = $data['fingerprint_pin'] ?: null;

        $shiftChanged = array_key_exists('shift_id', $data)
            && (int) ($data['shift_id'] ?? 0) !== (int) $employee->shift_id;
        $nonShiftChanged = array_key_exists('is_non_shift', $data)
            && (bool) $data['is_non_shift'] !== (bool) $employee->is_non_shift;
        $pinChanged = ($data['fingerprint_pin'] ?? null) !== $employee->fingerprint_pin;

        DB::transaction(function () use ($employee, $data) {
            $employee->update($data);
            $this->employeeUserSyncService->syncFromEmployee($employee->fresh());
        });

        if ($shiftChanged || $nonShiftChanged || $pinChanged) {
            $employee->load('shift');
            $this->fingerprintAttendanceService->queueSyncEmployeeToBranchDevices($employee);
        }

        return redirect()->route('employees.show', $employee)->with('success', 'Pegawai dan akun pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        DB::transaction(function () use ($employee) {
            $this->employeeUserSyncService->deleteLinkedUser($employee);
            $employee->delete();
        });

        return redirect()->route('employees.index')->with('success', 'Pegawai dan akun pengguna berhasil dihapus.');
    }

    public function storeAccount(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $employee->branch_id);

        if ($employee->user_id !== null) {
            return redirect()
                ->route('employees.index')
                ->with('warning', 'Pegawai ini sudah memiliki akun login.');
        }

        DB::transaction(function () use ($employee) {
            $this->employeeUserSyncService->syncFromEmployee($employee->fresh());
        });

        return redirect()
            ->route('employees.index')
            ->with('success', 'Akun login pegawai berhasil dibuat.');
    }

    /** @return array<string, mixed> */
    private function validatedEmployeeData(Request $request, ?Employee $employee = null): array
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'shift_selection' => ['required', 'string', $this->shiftSelectionRule()],
            'employee_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')->ignore($employee?->id),
            ],
            'fingerprint_pin' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'fingerprint_pin')
                    ->where(fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
                    ->ignore($employee?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($employee?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'employment_status' => ['required', 'string', 'in:permanent,contract,honorary'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'join_date' => ['nullable', 'date'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $shiftFields = Employee::shiftFieldsFromSelection($data['shift_selection']);
        unset($data['shift_selection']);

        return array_merge($data, $shiftFields);
    }

    private function shiftSelectionRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (in_array($value, ['non_shift', 'unset'], true)) {
                return;
            }

            if (! ctype_digit((string) $value) || ! Shift::query()->where('id', (int) $value)->where('is_active', true)->exists()) {
                $fail('Shift kerja tidak valid.');
            }
        };
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Shift> */
    private function activeShifts(?array $branchIds)
    {
        return Shift::query()
            ->with('branch')
            ->when($branchIds !== null, fn ($q) => $q->where(function ($inner) use ($branchIds) {
                $inner->whereIn('branch_id', $branchIds)->orWhereNull('branch_id');
            }))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
