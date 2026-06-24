<?php

namespace App\Http\Controllers\Web;

use App\Enums\ActivityLogAction;
use App\Enums\Permission;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends WebController
{
    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::ActivityLogView);

        $branchIds = $this->manageableBranchIds($request);
        $action = $request->string('action')->toString();
        $search = trim($request->string('search')->toString());
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $logs = ActivityLog::query()
            ->with(['user:id,name,email', 'branch:id,name'])
            ->when($branchIds !== null, fn ($query) => $query->where(function ($inner) use ($branchIds) {
                $inner->whereIn('branch_id', $branchIds)->orWhereNull('branch_id');
            }))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('user_name', 'like', "%{$search}%")
                        ->orWhere('user_email', 'like', "%{$search}%")
                        ->orWhere('subject_label', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%");
                });
            })
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($query) => $query->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $users = User::query()
            ->when($branchIds !== null, fn ($query) => $query->whereIn('branch_id', $branchIds))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $actions = ActivityLogAction::cases();

        return view('activity-logs.index', compact('logs', 'branches', 'users', 'actions', 'action', 'search', 'dateFrom', 'dateTo'));
    }
}
