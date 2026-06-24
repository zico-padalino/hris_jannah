<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Models\Branch;
use App\Models\Shift;
use App\Services\ShiftScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends WebController
{
    public function __construct(
        private readonly ShiftScheduleService $shiftScheduleService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::ShiftsManage);
        $branchIds = $this->manageableBranchIds($request);

        $shifts = Shift::query()
            ->with('branch')
            ->withCount('employees')
            ->when($branchIds !== null, fn ($q) => $q->where(function ($query) use ($branchIds) {
                $query->whereIn('branch_id', $branchIds)->orWhereNull('branch_id');
            }))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $statsQuery = Shift::query()
            ->when($branchIds !== null, fn ($q) => $q->where(function ($query) use ($branchIds) {
                $query->whereIn('branch_id', $branchIds)->orWhereNull('branch_id');
            }))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')));

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('is_active', true)->count(),
            'employees' => (clone $statsQuery)->withCount('employees')->get()->sum('employees_count'),
        ];

        return view('shifts.index', compact('shifts', 'branches', 'stats'));
    }

    public function create(Request $request): View
    {
        $this->authorizePermission($request, Permission::ShiftsManage);

        $branchIds = $this->manageableBranchIds($request);
        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('shifts.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::ShiftsManage);

        $data = $this->validatedShiftData($request);

        if ($data['branch_id']) {
            $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        }

        $shift = Shift::query()->create($data);
        $this->queueDeviceSync($shift);

        return redirect()->route('shifts.index')->with('success', 'Pengaturan jam kerja berhasil ditambahkan. Jadwal akan disinkronkan ke mesin fingerprint cabang terkait.');
    }

    public function edit(Request $request, Shift $shift): View
    {
        $this->authorizePermission($request, Permission::ShiftsManage);

        if ($shift->branch_id) {
            $this->authorizeBranchAccess($request, $shift->branch_id);
        }

        $shift->loadCount('employees');

        $branchIds = $this->manageableBranchIds($request);
        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('shifts.edit', compact('shift', 'branches'));
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizePermission($request, Permission::ShiftsManage);

        if ($shift->branch_id) {
            $this->authorizeBranchAccess($request, $shift->branch_id);
        }

        $shift->update($this->validatedShiftData($request));
        $this->queueDeviceSync($shift);

        return redirect()->route('shifts.index')->with('success', 'Pengaturan jam kerja berhasil diperbarui. Jadwal akan disinkronkan ke mesin fingerprint cabang terkait.');
    }

    public function destroy(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorizePermission($request, Permission::ShiftsManage);

        if ($shift->branch_id) {
            $this->authorizeBranchAccess($request, $shift->branch_id);
        }

        if ($shift->employees()->exists()) {
            return back()->with('error', 'Jam kerja masih dipakai pegawai. Pindahkan pegawai terlebih dahulu.');
        }

        $shift->delete();

        return redirect()->route('shifts.index')->with('success', 'Pengaturan jam kerja berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validatedShiftData(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'work_days' => ['required', 'array', 'min:1'],
            'work_days.*' => ['integer', 'between:1,7'],
            'late_tolerance_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        sort($data['work_days']);

        return [
            ...$data,
            'work_days' => array_values(array_unique($data['work_days'])),
            'is_active' => $request->boolean('is_active', true),
            'late_tolerance_minutes' => $data['late_tolerance_minutes'] ?? 15,
        ];
    }

    private function queueDeviceSync(Shift $shift): void
    {
        $this->shiftScheduleService->queueSyncShiftsForBranch($shift->branch_id);
    }
}
