<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Services\ModuleActionVisibilityService;
use App\Services\PermissionService;
use App\Services\SidebarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends WebController
{
    public function index(Request $request, PermissionService $permissionService): View
    {
        $this->authorizePermission($request, Permission::RolesView);

        $roles = UserRole::cases();
        $descriptions = $permissionService->roleDescriptions();

        return view('roles.index', compact('roles', 'descriptions'));
    }

    public function edit(Request $request, UserRole $role, PermissionService $permissionService): View|RedirectResponse
    {
        $this->authorizePermission($request, Permission::RolesView);

        if ($role->isProtected()) {
            return redirect()
                ->route('roles.index')
                ->with('error', __('pages.roles.edit_blocked'));
        }

        return view('roles.edit', [
            'role' => $role,
            'description' => $permissionService->descriptionForRole($role),
        ]);
    }

    public function update(Request $request, UserRole $role, PermissionService $permissionService): RedirectResponse
    {
        $this->authorizePermission($request, Permission::RolesView);

        if ($role->isProtected()) {
            return redirect()
                ->route('roles.index')
                ->with('error', __('pages.roles.edit_blocked'));
        }

        $data = $request->validate([
            'description' => ['required', 'string', 'max:500'],
        ]);

        $permissionService->saveRoleDescription($role, $data['description']);

        return redirect()
            ->route('roles.index')
            ->with('success', __('pages.roles.updated'));
    }

    public function permissions(
        Request $request,
        UserRole $role,
        PermissionService $permissionService,
        ModuleActionVisibilityService $moduleActionService,
        SidebarService $sidebarService,
    ): View {
        $this->authorizePermission($request, Permission::RolesView);

        $sidebarService->syncBuiltinVisibilityFromAllRoles($permissionService);

        $permissions = Permission::cases();
        $assigned = $role->isProtected()
            ? $permissions
            : $permissionService->forRole($role);
        $customModulesByGroup = $sidebarService->customModulesByPermissionGroup();
        $groupedPermissions = [];
        $permissionSections = [];

        foreach ($permissions as $permission) {
            $groupedPermissions[$permission->group()][] = $permission;
        }

        foreach ($groupedPermissions as $groupLabel => $items) {
            $permissionSections[$groupLabel] = [
                'permissions' => $items,
                'custom_modules' => $customModulesByGroup[$groupLabel] ?? [],
            ];
            unset($customModulesByGroup[$groupLabel]);
        }

        foreach ($customModulesByGroup as $groupLabel => $modules) {
            if ($modules === []) {
                continue;
            }

            $permissionSections[$groupLabel] = [
                'permissions' => [],
                'custom_modules' => $modules,
            ];
        }

        return view('roles.permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
            'permissionSections' => $permissionSections,
            'assigned' => $assigned,
            'actionModules' => $moduleActionService->modulesWithActions(),
            'visibleActionIds' => $moduleActionService->visibleActionIdsForRole($role),
            'matrix' => $permissionService->matrix(),
            'sidebarService' => $sidebarService,
        ]);
    }

    public function updatePermissions(
        Request $request,
        UserRole $role,
        PermissionService $permissionService,
        ModuleActionVisibilityService $moduleActionService,
        SidebarService $sidebarService,
    ): RedirectResponse {
        $this->authorizePermission($request, Permission::RolesView);

        if ($role->isProtected()) {
            return redirect()
                ->route('roles.permissions', $role)
                ->with('error', __('pages.roles.permissions_blocked'));
        }

        $validPermissions = array_map(
            fn (Permission $permission) => $permission->value,
            Permission::cases()
        );
        $validActionIds = $moduleActionService->modulesWithActions()
            ->flatMap(fn ($module) => $module->actions)
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($validPermissions)],
            'custom_modules' => ['nullable', 'array'],
            'custom_modules.*' => ['string', Rule::in($sidebarService->customModuleKeys())],
            'module_action_ids' => ['nullable', 'array'],
            'module_action_ids.*' => ['integer', Rule::in($validActionIds)],
        ]);

        $permissionService->saveRolePermissions($role, $data['permissions'] ?? []);
        $sidebarService->saveCustomModuleVisibilityForRole($role, $data['custom_modules'] ?? []);
        $sidebarService->syncBuiltinVisibilityFromPermissions($role, $data['permissions'] ?? []);
        $moduleActionService->saveForRole($role, $data['module_action_ids'] ?? []);

        return redirect()
            ->route('roles.permissions', $role)
            ->with('success', __('pages.roles.permissions_saved'));
    }

    public function storeActionModule(
        Request $request,
        ModuleActionVisibilityService $moduleActionService,
    ): RedirectResponse {
        $this->authorizePermission($request, Permission::RolesView);

        $data = $request->validate([
            'return_role' => ['required', Rule::enum(UserRole::class)],
            'module_key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/', 'unique:action_modules,module_key'],
            'label' => ['required', 'string', 'max:120'],
        ]);

        $moduleActionService->createModule($data['module_key'], $data['label']);

        return redirect()
            ->route('roles.permissions', $data['return_role'])
            ->with('success', __('pages.roles.module_created'));
    }

    public function storeModuleAction(
        Request $request,
        string $moduleKey,
        ModuleActionVisibilityService $moduleActionService,
    ): RedirectResponse {
        $this->authorizePermission($request, Permission::RolesView);

        $data = $request->validate([
            'return_role' => ['required', Rule::enum(UserRole::class)],
            'action_key' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('module_actions', 'action_key')->where(fn ($query) => $query->where('module_key', $moduleKey)),
            ],
            'label' => ['required', 'string', 'max:120'],
            'icon_type' => ['nullable', 'string', Rule::in(['view', 'edit', 'delete', 'location', 'extra'])],
        ]);

        $moduleActionService->createAction(
            $moduleKey,
            $data['action_key'],
            $data['label'],
            $data['icon_type'] ?? 'extra',
        );

        return redirect()
            ->route('roles.permissions', $data['return_role'])
            ->with('success', __('pages.roles.module_action_created'));
    }
}
