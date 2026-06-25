<?php

namespace App\Services;

use App\Enums\PayrollSlipSignatureStatus;
use App\Enums\Permission;
use App\Models\PayrollSlipSignature;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PayrollSlipBadgeService
{
    public function pendingApprovalCount(User $user): int
    {
        if (! $user->hasPermission(Permission::PayrollManage)) {
            return 0;
        }

        return $this->pendingApprovalQuery($user)->count();
    }

    /** @return Collection<int, PayrollSlipSignature> */
    public function recentPendingApprovals(User $user, int $limit = 5): Collection
    {
        if (! $user->hasPermission(Permission::PayrollManage)) {
            return collect();
        }

        return $this->pendingApprovalQuery($user)
            ->with(['payrollItem.employee', 'payrollItem.payrollPeriod', 'requestedBy'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function pendingApprovalQuery(User $user): Builder
    {
        return PayrollSlipSignature::query()
            ->where('status', PayrollSlipSignatureStatus::Pending)
            ->whereHas('payrollItem.employee', function ($query) use ($user) {
                $branchIds = $this->branchIds($user);

                if ($branchIds !== null) {
                    $query->whereIn('branch_id', $branchIds);
                }
            });
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
