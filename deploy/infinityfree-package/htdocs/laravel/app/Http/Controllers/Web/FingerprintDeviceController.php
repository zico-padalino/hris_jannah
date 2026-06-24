<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use App\Models\FingerprintLog;
use App\Models\Shift;
use App\Services\FingerprintAttendanceService;
use App\Services\ZktecoAdmsService;
use App\Services\ZktecoTcpPullService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FingerprintDeviceController extends WebController
{
    public function __construct(
        private readonly FingerprintAttendanceService $fingerprintAttendanceService,
        private readonly ZktecoAdmsService $admsService,
        private readonly ZktecoTcpPullService $tcpPullService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        $branchIds = $this->manageableBranchIds($request);

        $devices = FingerprintDevice::query()
            ->with('branch')
            ->withCount('logs')
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->latest('last_seen_at')
            ->paginate(15);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $serverUrl = $request->getSchemeAndHttpHost();
        $logSyncSnapshot = $this->indexLogSyncSnapshot($request);
        $fingerprintLogMode = config('attendance.fingerprint_log_mode', 'tcp');

        return view('fingerprint-devices.index', compact('devices', 'branches', 'serverUrl', 'logSyncSnapshot', 'fingerprintLogMode'));
    }

    public function edit(Request $request, FingerprintDevice $fingerprintDevice): View
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        if ($fingerprintDevice->branch_id) {
            $this->authorizeBranchAccess($request, $fingerprintDevice->branch_id);
        }

        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $recentLogs = FingerprintLog::query()
            ->with(['employee', 'attendance'])
            ->where('fingerprint_device_id', $fingerprintDevice->id)
            ->latest('punched_at')
            ->limit(20)
            ->get();

        $logSyncSnapshot = $this->deviceLogSyncSnapshot($fingerprintDevice);

        $branchShifts = $fingerprintDevice->branch_id
            ? Shift::query()
                ->where('is_active', true)
                ->where(function ($query) use ($fingerprintDevice) {
                    $query->whereNull('branch_id')
                        ->orWhere('branch_id', $fingerprintDevice->branch_id);
                })
                ->orderBy('name')
                ->get()
            : collect();

        $fingerprintLogMode = config('attendance.fingerprint_log_mode', 'tcp');

        return view('fingerprint-devices.edit', compact('fingerprintDevice', 'branches', 'recentLogs', 'logSyncSnapshot', 'branchShifts', 'fingerprintLogMode'));
    }

    public function update(Request $request, FingerprintDevice $fingerprintDevice): RedirectResponse
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_id' => ['required', 'exists:branches,id'],
            'ip_address' => ['nullable', 'ip'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        $data['is_active'] = $request->boolean('is_active', false);

        $fingerprintDevice->update($data);

        return redirect()
            ->route('fingerprint-devices.edit', $fingerprintDevice)
            ->with('success', 'Mesin fingerprint berhasil diperbarui.');
    }

    public function syncEmployees(Request $request, FingerprintDevice $fingerprintDevice): RedirectResponse
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        if (! $fingerprintDevice->branch_id) {
            return back()->with('error', 'Tetapkan cabang mesin terlebih dahulu.');
        }

        $this->authorizeBranchAccess($request, $fingerprintDevice->branch_id);

        $employees = Employee::query()
            ->where('branch_id', $fingerprintDevice->branch_id)
            ->where('is_active', true)
            ->whereNotNull('fingerprint_pin')
            ->with('shift')
            ->get();

        $this->fingerprintAttendanceService->queueFullSync($fingerprintDevice, $employees);

        return back()->with('success', "Sinkronisasi {$employees->count()} pegawai + jadwal jam kerja dijadwalkan ke mesin.");
    }

    public function syncShifts(Request $request, FingerprintDevice $fingerprintDevice): RedirectResponse
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        if (! $fingerprintDevice->branch_id) {
            return back()->with('error', 'Tetapkan cabang mesin terlebih dahulu.');
        }

        $this->authorizeBranchAccess($request, $fingerprintDevice->branch_id);

        $count = $this->fingerprintAttendanceService->queueSyncShifts($fingerprintDevice);

        return back()->with('success', "Sinkronisasi {$count} jadwal jam kerja dijadwalkan ke mesin.");
    }

    public function syncAll(Request $request, FingerprintDevice $fingerprintDevice): RedirectResponse
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        if (! $fingerprintDevice->branch_id) {
            return back()->with('error', 'Tetapkan cabang mesin terlebih dahulu.');
        }

        $this->authorizeBranchAccess($request, $fingerprintDevice->branch_id);

        $employees = Employee::query()
            ->where('branch_id', $fingerprintDevice->branch_id)
            ->where('is_active', true)
            ->whereNotNull('fingerprint_pin')
            ->with('shift')
            ->get();

        $shiftCount = $this->fingerprintAttendanceService->queueSyncShifts($fingerprintDevice);

        foreach ($employees as $employee) {
            $this->fingerprintAttendanceService->queueSyncEmployee($fingerprintDevice, $employee);
        }

        return back()->with('success', "Sinkron penuh dijadwalkan: {$shiftCount} jadwal + {$employees->count()} pegawai.");
    }

    public function pullLogs(Request $request, FingerprintDevice $fingerprintDevice): RedirectResponse
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        if ($fingerprintDevice->branch_id) {
            $this->authorizeBranchAccess($request, $fingerprintDevice->branch_id);
        }

        if (! extension_loaded('sockets')) {
            return back()->with('error', 'Ekstensi PHP "sockets" belum aktif. Aktifkan extension=sockets di php.ini lalu restart server Laravel.');
        }

        $result = $this->tcpPullService->pullFromDevice($fingerprintDevice);

        $flashKey = $result['processed'] > 0 ? 'success' : ($result['skipped'] > 0 ? 'success' : 'error');

        return back()->with($flashKey, $result['message']);
    }

    public function logSyncStatus(Request $request, FingerprintDevice $fingerprintDevice): JsonResponse
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        if ($fingerprintDevice->branch_id) {
            $this->authorizeBranchAccess($request, $fingerprintDevice->branch_id);
        }

        return response()->json($this->deviceLogSyncSnapshot($fingerprintDevice));
    }

    public function indexLogSyncStatus(Request $request): JsonResponse
    {
        $this->authorizePermission($request, Permission::FingerprintManage);

        return response()->json($this->indexLogSyncSnapshot($request));
    }

    /**
     * @return array{logs_count: int, latest_log_id: int}
     */
    private function deviceLogSyncSnapshot(FingerprintDevice $device): array
    {
        return [
            'logs_count' => (int) $device->logs()->count(),
            'latest_log_id' => (int) ($device->logs()->max('id') ?? 0),
        ];
    }

    /**
     * @return array{logs_count: int, latest_log_id: int}
     */
    private function indexLogSyncSnapshot(Request $request): array
    {
        $branchIds = $this->manageableBranchIds($request);

        $deviceIds = FingerprintDevice::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->pluck('id');

        $logsQuery = FingerprintLog::query()->whereIn('fingerprint_device_id', $deviceIds);

        return [
            'logs_count' => (int) $logsQuery->count(),
            'latest_log_id' => (int) ($logsQuery->max('id') ?? 0),
        ];
    }
}
