<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\FingerprintAttendanceService;
use App\Services\ShiftScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeShiftController extends WebController
{
    public function __construct(
        private readonly FingerprintAttendanceService $fingerprintAttendanceService,
        private readonly ShiftScheduleService $shiftScheduleService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::ShiftsManage);

        $branchIds = $this->manageableBranchIds($request);

        $employeesQuery = Employee::query()
            ->with(['branch', 'department', 'shift'])
            ->where('is_active', true)
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))
            ->when($request->query('shift_id') === 'unassigned', fn ($q) => $q->whereNull('shift_id')->where('is_non_shift', false))
            ->when($request->query('shift_id') === 'non_shift', fn ($q) => $q->where('is_non_shift', true))
            ->when(
                $request->filled('shift_id') && ! in_array($request->query('shift_id'), ['unassigned', 'non_shift'], true),
                fn ($q) => $q->where('shift_id', $request->integer('shift_id'))
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        $statsBase = Employee::query()
            ->where('is_active', true)
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')));

        $stats = [
            'total' => (clone $statsBase)->count(),
            'assigned' => (clone $statsBase)->whereNotNull('shift_id')->count(),
            'non_shift' => (clone $statsBase)->where('is_non_shift', true)->count(),
            'unassigned' => (clone $statsBase)->whereNull('shift_id')->where('is_non_shift', false)->count(),
        ];

        $employees = $employeesQuery->paginate(20)->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->with('branch')
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->orderBy('name')
            ->get();

        $shifts = Shift::query()
            ->with('branch')
            ->where('is_active', true)
            ->when($branchIds !== null, fn ($q) => $q->where(function ($inner) use ($branchIds) {
                $inner->whereIn('branch_id', $branchIds)->orWhereNull('branch_id');
            }))
            ->when($request->filled('branch_id'), fn ($q) => $q->where(function ($inner) use ($request) {
                $inner->where('branch_id', $request->integer('branch_id'))->orWhereNull('branch_id');
            }))
            ->orderBy('name')
            ->get();

        return view('employee-shifts.index', compact(
            'employees',
            'branches',
            'departments',
            'shifts',
            'stats',
        ));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizePermission($request, Permission::ShiftsManage);
        $this->authorizeBranchAccess($request, $employee->branch_id);

        $data = $request->validate([
            'shift_selection' => ['required', 'string', $this->shiftSelectionRule()],
        ]);

        $shiftFields = Employee::shiftFieldsFromSelection($data['shift_selection']);
        $this->assertShiftApplicable($shiftFields['shift_id'], $employee);

        $changed = $employee->shiftAssignmentChanged($shiftFields);
        $employee->update($shiftFields);

        if ($changed) {
            $employee->load('shift');
            $this->fingerprintAttendanceService->queueSyncEmployeeToBranchDevices($employee);
            $this->recalculateEmployeeAttendances($employee);
        }

        return back()->with('success', "Jam kerja {$employee->name} berhasil diperbarui.");
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::ShiftsManage);

        $data = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
            'shift_selection' => ['required', 'string', $this->shiftSelectionRule()],
        ]);

        $shiftFields = Employee::shiftFieldsFromSelection($data['shift_selection']);
        $branchIds = $this->manageableBranchIds($request);

        $employees = Employee::query()
            ->whereIn('id', $data['employee_ids'])
            ->where('is_active', true)
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->get();

        if ($employees->isEmpty()) {
            return back()->with('error', 'Tidak ada pegawai yang dapat diperbarui.');
        }

        $updated = 0;

        foreach ($employees as $employee) {
            $this->authorizeBranchAccess($request, $employee->branch_id);
            $this->assertShiftApplicable($shiftFields['shift_id'], $employee);

            if (! $employee->shiftAssignmentChanged($shiftFields)) {
                continue;
            }

            $employee->update($shiftFields);
            $employee->load('shift');
            $this->fingerprintAttendanceService->queueSyncEmployeeToBranchDevices($employee);
            $this->recalculateEmployeeAttendances($employee);
            $updated++;
        }

        return back()->with('success', "Jam kerja {$updated} pegawai berhasil diperbarui.");
    }

    private function recalculateEmployeeAttendances(Employee $employee): void
    {
        $employee->attendances()
            ->with('employee.shift')
            ->orderBy('id')
            ->chunkById(100, function ($attendances) {
                foreach ($attendances as $attendance) {
                    $this->shiftScheduleService->recalculateStoredAttendance($attendance);
                }
            });
    }

    private function assertShiftApplicable(?int $shiftId, Employee $employee): void
    {
        if ($shiftId === null) {
            return;
        }

        $shift = Shift::query()->find($shiftId);

        if ($shift === null || ! $shift->is_active) {
            throw ValidationException::withMessages([
                'shift_selection' => 'Jadwal jam kerja tidak valid.',
            ]);
        }

        if ($shift->branch_id !== null && $shift->branch_id !== $employee->branch_id) {
            throw ValidationException::withMessages([
                'shift_selection' => "Jadwal {$shift->name} tidak berlaku untuk cabang pegawai.",
            ]);
        }
    }

    private function shiftSelectionRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (in_array($value, ['non_shift', 'unset'], true)) {
                return;
            }

            if (! ctype_digit((string) $value) || ! Shift::query()->where('id', (int) $value)->where('is_active', true)->exists()) {
                $fail('Jadwal jam kerja tidak valid.');
            }
        };
    }
}
