<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Models\Announcement;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends WebController
{
    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::AnnouncementsManage);
        $branchIds = $this->manageableBranchIds($request);

        $announcements = Announcement::query()
            ->with(['branch', 'creator'])
            ->when($branchIds !== null, fn ($q) => $q->where(function ($inner) use ($branchIds) {
                $inner->whereIn('branch_id', $branchIds)->orWhereNull('branch_id');
            }))
            ->when($request->filled('branch_id'), function ($q) use ($request) {
                $branchId = $request->integer('branch_id');
                $q->where(fn ($inner) => $inner->where('branch_id', $branchId)->orWhereNull('branch_id'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $status = $request->string('status')->toString();
                $today = today()->toDateString();

                match ($status) {
                    'active' => $q->where('is_active', true)
                        ->whereDate('starts_at', '<=', $today)
                        ->whereDate('ends_at', '>=', $today),
                    'scheduled' => $q->where('is_active', true)->whereDate('starts_at', '>', $today),
                    'expired' => $q->whereDate('ends_at', '<', $today),
                    'inactive' => $q->where('is_active', false),
                    default => null,
                };
            })
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('announcements.index', compact('announcements', 'branches'));
    }

    public function create(Request $request): View
    {
        $this->authorizePermission($request, Permission::AnnouncementsManage);

        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('announcements.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::AnnouncementsManage);

        $data = $this->validatedAnnouncementData($request);

        if ($data['branch_id']) {
            $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        }

        Announcement::query()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('announcements.index')->with('success', __('pages.announcements.created'));
    }

    public function edit(Request $request, Announcement $announcement): View
    {
        $this->authorizePermission($request, Permission::AnnouncementsManage);

        if ($announcement->branch_id) {
            $this->authorizeBranchAccess($request, $announcement->branch_id);
        }

        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('announcements.edit', compact('announcement', 'branches'));
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizePermission($request, Permission::AnnouncementsManage);

        if ($announcement->branch_id) {
            $this->authorizeBranchAccess($request, $announcement->branch_id);
        }

        $data = $this->validatedAnnouncementData($request);

        if ($data['branch_id']) {
            $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        }

        $announcement->update($data);

        return redirect()->route('announcements.index')->with('success', __('pages.announcements.updated'));
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizePermission($request, Permission::AnnouncementsManage);

        if ($announcement->branch_id) {
            $this->authorizeBranchAccess($request, $announcement->branch_id);
        }

        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', __('pages.announcements.deleted'));
    }

    /** @return array<string, mixed> */
    private function validatedAnnouncementData(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return [
            ...$data,
            'branch_id' => $data['branch_id'] ?: null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
