<?php

namespace App\Http\Controllers\Web;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends WebController
{
    public function index(Request $request): View
    {
        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->withCount(['employees', 'locations', 'departments'])
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->latest()
            ->paginate(10);

        return view('branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('branches.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Branch::query()->create($data);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function show(Request $request, Branch $branch): View
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $branch->load(['locations', 'departments']);

        return view('branches.show', compact('branch'));
    }

    public function edit(Request $request, Branch $branch): View
    {
        $this->authorizeBranchAccess($request, $branch->id);

        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:branches,code,'.$branch->id],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', false);

        $branch->update($data);

        return redirect()->route('branches.show', $branch)->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $branch->id);

        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
