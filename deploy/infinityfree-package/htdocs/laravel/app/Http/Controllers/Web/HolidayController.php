<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Models\Branch;
use App\Models\Holiday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HolidayController extends WebController
{
    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::HolidaysManage);
        $branchIds = $this->manageableBranchIds($request);

        $holidays = Holiday::query()
            ->with('branch')
            ->when($branchIds !== null, fn ($q) => $q->where(function ($inner) use ($branchIds) {
                $inner->whereIn('branch_id', $branchIds)->orWhereNull('branch_id');
            }))
            ->when($request->filled('branch_id'), function ($q) use ($request) {
                $branchId = $request->integer('branch_id');
                $q->where(fn ($inner) => $inner->where('branch_id', $branchId)->orWhereNull('branch_id'));
            })
            ->orderByDesc('date')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('holidays.index', compact('holidays', 'branches'));
    }

    public function create(Request $request): View
    {
        $this->authorizePermission($request, Permission::HolidaysManage);

        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('holidays.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::HolidaysManage);

        $data = $this->validatedHolidayData($request);

        if ($data['branch_id']) {
            $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        }

        Holiday::query()->create($data);

        return redirect()->route('holidays.index')->with('success', 'Hari libur berhasil ditambahkan.');
    }

    public function edit(Request $request, Holiday $holiday): View
    {
        $this->authorizePermission($request, Permission::HolidaysManage);

        if ($holiday->branch_id) {
            $this->authorizeBranchAccess($request, $holiday->branch_id);
        }

        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('holidays.edit', compact('holiday', 'branches'));
    }

    public function update(Request $request, Holiday $holiday): RedirectResponse
    {
        $this->authorizePermission($request, Permission::HolidaysManage);

        if ($holiday->branch_id) {
            $this->authorizeBranchAccess($request, $holiday->branch_id);
        }

        $data = $this->validatedHolidayData($request);

        if ($data['branch_id']) {
            $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        }

        $holiday->update($data);

        return redirect()->route('holidays.index')->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(Request $request, Holiday $holiday): RedirectResponse
    {
        $this->authorizePermission($request, Permission::HolidaysManage);

        if ($holiday->branch_id) {
            $this->authorizeBranchAccess($request, $holiday->branch_id);
        }

        $holiday->delete();

        return redirect()->route('holidays.index')->with('success', 'Hari libur berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validatedHolidayData(Request $request): array
    {
        $data = $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        return [
            ...$data,
            'branch_id' => $data['branch_id'] ?: null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
