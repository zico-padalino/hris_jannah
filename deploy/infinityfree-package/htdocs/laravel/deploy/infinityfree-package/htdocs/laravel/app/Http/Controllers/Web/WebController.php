<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class WebController extends Controller
{
    protected function authorizeBranchAccess(Request $request, int $branchId): void
    {
        if (! $request->user()->canManageBranch($branchId)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }
    }

    protected function authorizePermission(Request $request, \App\Enums\Permission $permission): void
    {
        if (! $request->user()->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki akses ke fitur ini.');
        }
    }

    protected function manageableBranchIds(Request $request): ?array
    {
        $user = $request->user();

        if ($user->isSuperAdmin() || $user->isHr()) {
            return null;
        }

        return $user->branch_id ? [$user->branch_id] : [];
    }
}
