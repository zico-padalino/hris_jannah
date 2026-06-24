<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AnnouncementService
{
    /** @return Collection<int, Announcement> */
    public function forDashboard(User $user, int $limit = 5): Collection
    {
        $branchId = $this->userBranchId($user);

        return Announcement::query()
            ->with('branch')
            ->where('is_active', true)
            ->whereDate('starts_at', '<=', today())
            ->whereDate('ends_at', '>=', today())
            ->when($branchId !== null, fn (Builder $q) => $q->where(function (Builder $inner) use ($branchId) {
                $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }))
            ->orderByDesc('starts_at')
            ->limit($limit)
            ->get();
    }

    public function userBranchId(User $user): ?int
    {
        if ($user->isSuperAdmin() || $user->isHr()) {
            return null;
        }

        return $user->employee?->branch_id ?? $user->branch_id;
    }
}
