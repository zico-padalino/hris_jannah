<?php

namespace App\Http\Controllers\Web;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends WebController
{
    public function index(Request $request): View
    {
        $branchIds = $this->manageableBranchIds($request);

        $departments = Department::query()
            ->with('branch')
            ->withCount('employees')
            ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('departments.index', compact('departments', 'branches'));
    }

    public function create(Request $request): View
    {
        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedBranchId = $request->filled('branch_id') ? $request->integer('branch_id') : null;

        return view('departments.create', compact('branches', 'selectedBranchId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments', 'code')->where('branch_id', $request->integer('branch_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        $data['is_active'] = $request->boolean('is_active', true);

        Department::query()->create($data);

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function edit(Request $request, Department $department): View
    {
        $this->authorizeBranchAccess($request, $department->branch_id);

        $branchIds = $this->manageableBranchIds($request);

        $branches = Branch::query()
            ->when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('departments.edit', compact('department', 'branches'));
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $department->branch_id);

        $data = $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments', 'code')
                    ->where('branch_id', $request->integer('branch_id'))
                    ->ignore($department->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $this->authorizeBranchAccess($request, (int) $data['branch_id']);
        $data['is_active'] = $request->boolean('is_active', false);

        $department->update($data);

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil diperbarui.');
    }

    public function destroy(Request $request, Department $department): RedirectResponse
    {
        $this->authorizeBranchAccess($request, $department->branch_id);

        if ($department->employees()->exists()) {
            return back()->with('error', 'Departemen masih memiliki pegawai. Pindahkan pegawai terlebih dahulu.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil dihapus.');
    }
}
