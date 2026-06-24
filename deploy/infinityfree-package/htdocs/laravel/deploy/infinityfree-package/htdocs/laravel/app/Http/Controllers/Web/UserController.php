<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Models\Branch;
use App\Models\User;
use App\Services\EmployeeUserSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends WebController
{
    public function __construct(
        private readonly EmployeeUserSyncService $employeeUserSyncService,
    ) {}
    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::UsersManage);

        $users = User::query()
            ->with('branch')
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(Request $request): View
    {
        $this->authorizePermission($request, \App\Enums\Permission::UsersManage);
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        return view('users.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::UsersManage);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                Rule::requiredIf(fn () => $request->input('role') !== 'employee'),
                'nullable',
                'string',
                'min:6',
            ],
            'role' => ['required', 'in:super_admin,hr,branch_admin,employee'],
            'branch_id' => [
                Rule::requiredIf(fn () => in_array($request->input('role'), ['branch_admin', 'employee'], true)),
                'nullable',
                'exists:branches,id',
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        if (in_array($data['role'], ['super_admin', 'hr'], true)) {
            $data['branch_id'] = null;
        }

        $plainPassword = $request->filled('password') ? $request->string('password')->toString() : null;

        DB::transaction(function () use ($data, $plainPassword) {
            $data['password'] = bcrypt($plainPassword ?? Str::random(32));

            $user = User::query()->create($data);
            $this->employeeUserSyncService->syncFromUser($user);

            if ($plainPassword === null) {
                $this->employeeUserSyncService->applyDefaultPassword($user->fresh(), null);
            }
        });

        return redirect()->route('users.index')->with('success', 'Pengguna dan data pegawai berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizePermission($request, \App\Enums\Permission::UsersManage);
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizePermission($request, Permission::UsersManage);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:super_admin,hr,branch_admin,employee'],
            'branch_id' => [
                Rule::requiredIf(fn () => in_array($request->input('role'), ['branch_admin', 'employee'], true)),
                'nullable',
                'exists:branches,id',
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', false);

        if (in_array($data['role'], ['super_admin', 'hr'], true)) {
            $data['branch_id'] = null;
        }

        if (! empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        DB::transaction(function () use ($user, $data) {
            $user->update($data);
            $this->employeeUserSyncService->syncFromUser($user->fresh());
        });

        return redirect()->route('users.index')->with('success', 'Pengguna dan data pegawai berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizePermission($request, Permission::UsersManage);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        DB::transaction(function () use ($user) {
            $this->employeeUserSyncService->deleteLinkedEmployee($user);
            $user->delete();
        });

        return redirect()->route('users.index')->with('success', 'Pengguna dan data pegawai berhasil dihapus.');
    }
}
