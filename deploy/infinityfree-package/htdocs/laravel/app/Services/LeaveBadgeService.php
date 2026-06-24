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
        $employee = $user->employee;

        if ($employee === null) {
            return 0;
        }

        if (! $user->hasPermission(Permission::LeaveRequest)
            && ! $user->hasPermission(Permission::LeaveViewOwn)) {
            return 0;
        }

        return LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', LeaveStatus::Pending)
            ->count();
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
