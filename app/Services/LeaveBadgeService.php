<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Enums\Permission;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LeaveBadgeService
{
    public function pendingApprovalCount(User $user): int
    {
        return $this->pendingApprovalBreakdown($user)['total'];
    }

    /**
     * @return array{cuti: int, izin: int, lembur: int, total: int}
     */
    public function pendingApprovalBreakdown(User $user): array
    {
        $empty = ['cuti' => 0, 'izin' => 0, 'lembur' => 0, 'total' => 0];

        if (! $user->hasPermission(Permission::LeaveApprove)) {
            return $empty;
        }

        $query = $this->pendingApprovalQuery($user);

        return [
            'cuti' => (clone $query)->whereIn('type', [LeaveType::Annual, LeaveType::Sick])->count(),
            'izin' => (clone $query)->where('type', LeaveType::Permission)->count(),
            'lembur' => (clone $query)->where('type', LeaveType::Overtime)->count(),
            'total' => (clone $query)->count(),
        ];
    }

    /** @return Collection<int, LeaveRequest> */
    public function recentPendingApprovals(User $user, int $limit = 5): Collection
    {
        if (! $user->hasPermission(Permission::LeaveApprove)) {
            return collect();
        }

        return $this->pendingApprovalQuery($user)
            ->with(['employee', 'branch'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function pendingOwnCount(User $user): int
    {
        return $this->pendingOwnBreakdown($user)['total'];
    }

    /**
     * @return array{cuti: int, izin: int, lembur: int, total: int}
     */
    public function pendingOwnBreakdown(User $user): array
    {
        $empty = ['cuti' => 0, 'izin' => 0, 'lembur' => 0, 'total' => 0];

        $employee = $user->employee;

        if ($employee === null) {
            return $empty;
        }

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            return $empty;
        }

        $baseQuery = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', LeaveStatus::Pending);

        return [
            'cuti' => (clone $baseQuery)->whereIn('type', LeaveType::forApprovalCategory('cuti'))->count(),
            'izin' => (clone $baseQuery)->whereIn('type', LeaveType::forApprovalCategory('izin'))->count(),
            'lembur' => (clone $baseQuery)->whereIn('type', LeaveType::forApprovalCategory('lembur'))->count(),
            'total' => (clone $baseQuery)->count(),
        ];
    }

    public function unreadOwnStatusCount(User $user): int
    {
        return $this->unreadOwnStatusBreakdown($user)['total'];
    }

    /**
     * @return array{approved: int, rejected: int, total: int}
     */
    public function unreadOwnStatusBreakdown(User $user): array
    {
        $empty = ['approved' => 0, 'rejected' => 0, 'total' => 0];

        $employee = $user->employee;

        if ($employee === null) {
            return $empty;
        }

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            return $empty;
        }

        $query = $this->unreadOwnStatusQuery($employee->id);

        return [
            'approved' => (clone $query)->where('status', LeaveStatus::Approved)->count(),
            'rejected' => (clone $query)->where('status', LeaveStatus::Rejected)->count(),
            'total' => (clone $query)->count(),
        ];
    }

    /**
     * @return array{
     *     cuti: array{approved: int, rejected: int, total: int},
     *     izin: array{approved: int, rejected: int, total: int},
     *     lembur: array{approved: int, rejected: int, total: int},
     *     total: int
     * }
     */
    public function unreadOwnStatusModuleBreakdown(User $user): array
    {
        $emptyModule = ['approved' => 0, 'rejected' => 0, 'total' => 0];
        $empty = [
            'cuti' => $emptyModule,
            'izin' => $emptyModule,
            'lembur' => $emptyModule,
            'total' => 0,
        ];

        $employee = $user->employee;

        if ($employee === null) {
            return $empty;
        }

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            return $empty;
        }

        $result = $empty;

        foreach (['cuti', 'izin', 'lembur'] as $category) {
            $query = $this->unreadOwnStatusQuery($employee->id)
                ->whereIn('type', LeaveType::forApprovalCategory($category));

            $result[$category] = [
                'approved' => (clone $query)->where('status', LeaveStatus::Approved)->count(),
                'rejected' => (clone $query)->where('status', LeaveStatus::Rejected)->count(),
                'total' => (clone $query)->count(),
            ];
        }

        $result['total'] = $result['cuti']['total'] + $result['izin']['total'] + $result['lembur']['total'];

        return $result;
    }

    /** @return Collection<int, LeaveRequest> */
    public function recentUnreadOwnStatuses(User $user, int $limit = 5): Collection
    {
        $employee = $user->employee;

        if ($employee === null) {
            return collect();
        }

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            return collect();
        }

        return $this->unreadOwnStatusQuery($employee->id)
            ->with(['branch', 'approver'])
            ->latest('approved_at')
            ->limit($limit)
            ->get();
    }

    public function markOwnStatusRead(User $user, int $leaveId): void
    {
        $employee = $user->employee;

        if ($employee === null) {
            return;
        }

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            return;
        }

        LeaveRequest::query()
            ->whereKey($leaveId)
            ->where('employee_id', $employee->id)
            ->whereIn('status', [LeaveStatus::Approved, LeaveStatus::Rejected])
            ->whereNull('employee_status_read_at')
            ->update(['employee_status_read_at' => now()]);
    }

    public function markAllOwnStatusesRead(User $user): void
    {
        $employee = $user->employee;

        if ($employee === null) {
            return;
        }

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            return;
        }

        $this->unreadOwnStatusQuery($employee->id)
            ->update(['employee_status_read_at' => now()]);
    }

    public function markOwnStatusesReadForCategory(User $user, string $category): void
    {
        if (! in_array($category, ['cuti', 'izin', 'lembur'], true)) {
            return;
        }

        $employee = $user->employee;

        if ($employee === null) {
            return;
        }

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            return;
        }

        $this->unreadOwnStatusQuery($employee->id)
            ->whereIn('type', LeaveType::forApprovalCategory($category))
            ->update(['employee_status_read_at' => now()]);
    }

    private function unreadOwnStatusQuery(int $employeeId): Builder
    {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', [LeaveStatus::Approved, LeaveStatus::Rejected])
            ->whereNull('employee_status_read_at');
    }

    private function pendingApprovalQuery(User $user): Builder
    {
        return LeaveRequest::query()
            ->where('status', LeaveStatus::Pending)
            ->when($this->branchIds($user) !== null, fn ($q) => $q->whereIn('branch_id', $this->branchIds($user)));
    }

    /** @return list<int>|null */
    private function branchIds(User $user): ?array
    {
        if ($user->isSuperAdmin() || $user->isHr()) {
            return null;
        }

        return $user->branch_id ? [$user->branch_id] : [];
    }
}
