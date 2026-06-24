<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\ActionModule;
use App\Models\ModuleAction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModuleActionVisibilityService
{
    private const CACHE_KEY = 'role_module_action_visibility_v1';

    /** @var array<string, array<string, array<string, true>>> */
    private array $matrix;

    public function __construct()
    {
        $this->matrix = $this->loadMatrix();
    }

    public function userCanSee(User $user, string $moduleKey, string $actionKey): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return isset($this->matrix[$user->role->value][$moduleKey][$actionKey]);
    }

    /** @return Collection<int, ActionModule> */
    public function modulesWithActions(): Collection
    {
        return ActionModule::query()
            ->with('actions')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** @return list<int> */
    public function visibleActionIdsForRole(UserRole $role): array
    {
        if ($role === UserRole::SuperAdmin) {
            return ModuleAction::query()->pluck('id')->all();
        }

        return DB::table('role_module_action_visibility')
            ->where('role', $role->value)
            ->pluck('module_action_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** @param list<int|string> $moduleActionIds */
    public function saveForRole(UserRole $role, array $moduleActionIds): void
    {
        if ($role === UserRole::SuperAdmin) {
            return;
        }

        $validIds = ModuleAction::query()
            ->whereIn('id', $moduleActionIds)
            ->pluck('id')
            ->all();

        DB::transaction(function () use ($role, $validIds) {
            DB::table('role_module_action_visibility')
                ->where('role', $role->value)
                ->delete();

            $now = now();

            foreach ($validIds as $id) {
                DB::table('role_module_action_visibility')->insert([
                    'role' => $role->value,
                    'module_action_id' => $id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        $this->forgetCache();
    }

    public function createModule(string $moduleKey, string $label): ActionModule
    {
        $moduleKey = Str::slug($moduleKey, '_');

        return ActionModule::query()->create([
            'module_key' => $moduleKey,
            'label' => trim($label),
            'sort_order' => (int) ActionModule::query()->max('sort_order') + 10,
        ]);
    }

    public function createAction(
        string $moduleKey,
        string $actionKey,
        string $label,
        string $iconType = 'extra',
    ): ModuleAction {
        $actionKey = Str::slug($actionKey, '_');
        $iconType = $this->normalizeIconType($iconType);

        $action = ModuleAction::query()->create([
            'module_key' => $moduleKey,
            'action_key' => $actionKey,
            'label' => trim($label),
            'icon_type' => $iconType,
            'sort_order' => (int) ModuleAction::query()
                ->where('module_key', $moduleKey)
                ->max('sort_order') + 10,
        ]);

        $this->grantNewActionToDefaultRoles($action);

        return $action;
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->matrix = $this->loadMatrix();
    }

    private function grantNewActionToDefaultRoles(ModuleAction $action): void
    {
        $now = now();

        foreach ([UserRole::Hr, UserRole::BranchAdmin] as $role) {
            DB::table('role_module_action_visibility')->insertOrIgnore([
                'role' => $role->value,
                'module_action_id' => $action->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($action->action_key === 'view') {
            DB::table('role_module_action_visibility')->insertOrIgnore([
                'role' => UserRole::Employee->value,
                'module_action_id' => $action->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->forgetCache();
    }

  /** @return array<string, array<string, array<string, true>>> */
    private function loadMatrix(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            if (! $this->tablesExist()) {
                return [];
            }

            $rows = DB::table('role_module_action_visibility as visibility')
                ->join('module_actions as actions', 'actions.id', '=', 'visibility.module_action_id')
                ->select([
                    'visibility.role',
                    'actions.module_key',
                    'actions.action_key',
                ])
                ->get();

            $matrix = [];

            foreach ($rows as $row) {
                $matrix[$row->role][$row->module_key][$row->action_key] = true;
            }

            return $matrix;
        });
    }

    private function tablesExist(): bool
    {
        return DB::getSchemaBuilder()->hasTable('role_module_action_visibility')
            && DB::getSchemaBuilder()->hasTable('module_actions');
    }

    private function normalizeIconType(string $iconType): string
    {
        $allowed = ['view', 'edit', 'delete', 'location', 'extra'];

        return in_array($iconType, $allowed, true) ? $iconType : 'extra';
    }
}
