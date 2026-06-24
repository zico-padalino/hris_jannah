<?php

namespace App\Services;

use App\Enums\Permission;
use App\Enums\SidebarNavItem;
use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\SidebarMenuRepository;
use Illuminate\Support\Facades\Cache;

class SidebarService
{
    private const CACHE_KEY_POSITION = 'sidebar_position_v3';

    private const CACHE_KEY_VISIBILITY = 'sidebar_visibility_matrix_v3';

    private const CACHE_KEY_GROUPS = 'sidebar_groups_layout_v3';

    private const CACHE_KEY_MODULES = 'sidebar_custom_modules_v3';

    private string $position;

    /** @var array<string, array<string, bool>> */
    private array $visibility;

    /** @var list<array{id: string, builtin: ?string, label: ?string, items: list<string>}> */
    private array $groups;

    /** @var array<string, array{label: string, url: string}> */
    private array $customModules;

    public function __construct(private SidebarMenuRepository $menuRepository)
    {
        $this->position = $this->loadPosition();
        $this->visibility = $this->loadVisibility();
        $this->customModules = $this->loadCustomModules();
        $this->groups = $this->loadGroups();
    }

    public static function isCustomModuleKey(string $key): bool
    {
        return str_starts_with($key, 'mod_');
    }

    /** @return array<string, array{label: string, url: string}> */
    public function customModules(): array
    {
        return $this->customModules;
    }

    /** @return list<array{value: string, label: string}> */
    public function builtinModuleOptions(): array
    {
        $options = [];

        foreach ([SidebarNavItem::Dashboard, ...SidebarNavItem::linkItems()] as $item) {
            $options[] = [
                'value' => $item->value,
                'label' => __($item->navLabelKey()),
            ];
        }

        return $options;
    }

    public function position(): string
    {
        return $this->position;
    }

    public function isRight(): bool
    {
        return $this->position === 'right';
    }

    /** @return list<array{id: string, builtin: ?string, label: ?string, items: list<string>}> */
    public function groups(): array
    {
        return $this->groups;
    }

    /** @return list<SidebarNavItem> */
    public function orderedNavItems(): array
    {
        $items = [];

        foreach ($this->groups as $group) {
            foreach ($group['items'] as $key) {
                $item = SidebarNavItem::tryFrom($key);

                if ($item !== null && ! in_array($item, $items, true)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    public function visible(User $user, SidebarNavItem $item): bool
    {
        if ($item->isSection()) {
            return $this->sectionVisible($user, $item);
        }

        if (! $this->userMeetsPermission($user, $item)) {
            return false;
        }

        return $this->roleAllowed($item, $user->role);
    }

    public function sectionVisible(User $user, SidebarNavItem $section): bool
    {
        if (! $section->isSection()) {
            return false;
        }

        foreach ($section->children() as $child) {
            if ($this->visible($user, $child)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, array<string, bool>> */
    public function visibilityMatrix(): array
    {
        return $this->visibility;
    }

    public function groupLabel(array $group): string
    {
        if (! empty($group['label'])) {
            return $group['label'];
        }

        if (! empty($group['builtin']) && $group['builtin'] !== 'dashboard') {
            $section = SidebarNavItem::tryFrom($group['builtin']);

            if ($section !== null) {
                return __($section->navLabelKey());
            }
        }

        return '';
    }

    public function defaultGroupIdForItem(SidebarNavItem $item): string
    {
        if ($item === SidebarNavItem::Dashboard) {
            return 'grp_dashboard';
        }

        $section = $item->parentSection();

        return $section !== null ? 'grp_'.$section->value : 'grp_custom_misc';
    }

    public function defaultGroupIdForKey(string $key): string
    {
        if (self::isCustomModuleKey($key)) {
            return 'grp_custom_misc';
        }

        $item = SidebarNavItem::tryFrom($key);

        return $item !== null ? $this->defaultGroupIdForItem($item) : 'grp_custom_misc';
    }

    public function visibleModule(User $user, string $moduleKey): bool
    {
        if (! self::isCustomModuleKey($moduleKey) || ! isset($this->customModules[$moduleKey])) {
            return false;
        }

        if (! $user->is_active) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $this->visibility[$moduleKey][$user->role->value] ?? true;
    }

    /**
     * @return list<array{
     *     id: string,
     *     label: string,
     *     builtin: ?string,
     *     collapsible: bool,
     *     items: list<array{type: string, item?: SidebarNavItem, module?: array{label: string, url: string}, module_key?: string}>
     * }>
     */
    public function navigationGroups(User $user): array
    {
        $groups = [];

        foreach ($this->groups as $group) {
            $visibleItems = [];

            foreach ($group['items'] as $key) {
                if (self::isCustomModuleKey($key)) {
                    if ($this->visibleModule($user, $key)) {
                        $visibleItems[] = [
                            'type' => 'custom',
                            'key' => $key,
                            'module' => $this->customModules[$key],
                        ];
                    }

                    continue;
                }

                $item = SidebarNavItem::tryFrom($key);

                if ($item === null || ! $this->visible($user, $item)) {
                    continue;
                }

                $visibleItems[] = ['type' => 'builtin', 'item' => $item];
            }

            if ($visibleItems === []) {
                continue;
            }

            $isDashboardGroup = ($group['builtin'] ?? null) === 'dashboard';
            $label = $isDashboardGroup ? '' : $this->groupLabel($group);

            $items = [];

            foreach ($visibleItems as $visibleItem) {
                if ($visibleItem['type'] === 'custom') {
                    $items[] = [
                        'type' => 'custom_link',
                        'module_key' => $visibleItem['key'],
                        'module' => $visibleItem['module'],
                    ];
                } else {
                    $items[] = ['type' => 'link', 'item' => $visibleItem['item']];
                }
            }

            $groups[] = [
                'id' => $group['id'],
                'label' => $label,
                'builtin' => $group['builtin'] ?? null,
                'collapsible' => ! $isDashboardGroup && $label !== '',
                'items' => $items,
            ];
        }

        return $groups;
    }

    /**
     * @return list<array{type: string, item?: SidebarNavItem, label?: string, builtin?: ?string, module?: array{label: string, url: string}, module_key?: string}>
     */
    public function navigationEntries(User $user): array
    {
        $entries = [];

        foreach ($this->navigationGroups($user) as $group) {
            if ($group['collapsible']) {
                $entries[] = [
                    'type' => 'section',
                    'label' => $group['label'],
                    'builtin' => $group['builtin'],
                ];
            }

            foreach ($group['items'] as $item) {
                $entries[] = $item;
            }
        }

        return $entries;
    }

    public function savePosition(string $position): void
    {
        $this->menuRepository->savePosition($position);

        Cache::forget(self::CACHE_KEY_POSITION);
        $this->position = $position;
    }

    /** @param array<string, list<string>> $input */
    public function saveVisibility(array $input): void
    {
        $matrix = [];

        foreach ([SidebarNavItem::Dashboard, ...SidebarNavItem::linkItems()] as $item) {
            $matrix[$item->value] = [];

            foreach (UserRole::cases() as $role) {
                if ($role === UserRole::SuperAdmin) {
                    $matrix[$item->value][$role->value] = true;

                    continue;
                }

                $enabled = in_array($item->value, $input[$role->value] ?? [], true);
                $matrix[$item->value][$role->value] = $enabled;
            }
        }

        foreach (array_keys($this->customModules) as $moduleKey) {
            $matrix[$moduleKey] = [];

            foreach (UserRole::cases() as $role) {
                if ($role === UserRole::SuperAdmin) {
                    $matrix[$moduleKey][$role->value] = true;

                    continue;
                }

                $matrix[$moduleKey][$role->value] = in_array($moduleKey, $input[$role->value] ?? [], true);
            }
        }

        foreach ($input as $roleKey => $keys) {
            if (! is_array($keys)) {
                continue;
            }

            foreach ($keys as $key) {
                if (! is_string($key) || ! self::isCustomModuleKey($key) || isset($matrix[$key])) {
                    continue;
                }

                $matrix[$key] = [];

                foreach (UserRole::cases() as $role) {
                    $matrix[$key][$role->value] = $role === UserRole::SuperAdmin
                        || in_array($key, $input[$role->value] ?? [], true);
                }
            }
        }

        $this->menuRepository->saveVisibility($matrix);

        Cache::forget(self::CACHE_KEY_VISIBILITY);
        $this->visibility = $matrix;
    }

    /**
     * @param  array<string, array{label?: string, url?: string}>  $input
     * @param  list<string>  $usedKeys
     */
    public function saveCustomModules(array $input, array $usedKeys): void
    {
        $modules = [];

        foreach ($usedKeys as $key) {
            if (! self::isCustomModuleKey($key)) {
                continue;
            }

            $row = $input[$key] ?? null;

            if (! is_array($row)) {
                if (isset($this->customModules[$key])) {
                    $modules[$key] = $this->customModules[$key];
                }

                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));

            if ($label === '' || $url === '') {
                continue;
            }

            $modules[$key] = [
                'label' => $label,
                'url' => $url,
            ];
        }

        $this->menuRepository->saveCustomModules($modules);

        Cache::forget(self::CACHE_KEY_MODULES);
        $this->customModules = $modules;
    }

    /**
     * @param  list<string>  $order
     * @param  array<string, array{builtin?: ?string, label?: ?string, items?: list<string>}>  $groupsInput
     */
    public function saveGroups(array $order, array $groupsInput): void
    {
        $normalized = [];
        $assigned = [];

        foreach ($order as $groupId) {
            if (! is_string($groupId) || ! isset($groupsInput[$groupId])) {
                continue;
            }

            $input = $groupsInput[$groupId];
            $items = [];

            foreach ($input['items'] ?? [] as $key) {
                if (! is_string($key) || in_array($key, $assigned, true)) {
                    continue;
                }

                if (self::isCustomModuleKey($key)) {
                    $items[] = $key;
                    $assigned[] = $key;

                    continue;
                }

                $item = SidebarNavItem::tryFrom($key);

                if ($item === null || $item->isSection()) {
                    continue;
                }

                $items[] = $key;
                $assigned[] = $key;
            }

            $normalized[] = [
                'id' => $groupId,
                'builtin' => isset($input['builtin']) && is_string($input['builtin']) ? $input['builtin'] : null,
                'label' => isset($input['label']) && is_string($input['label']) ? trim($input['label']) ?: null : null,
                'items' => $items,
            ];
        }

        foreach (SidebarNavItem::defaultNavOrder() as $item) {
            if (in_array($item->value, $assigned, true)) {
                continue;
            }

            $groupId = $this->defaultGroupIdForItem($item);
            $index = $this->findGroupIndex($normalized, $groupId);

            if ($index === null) {
                $normalized[] = $this->defaultGroupDefinition($groupId);
                $index = count($normalized) - 1;
            }

            $normalized[$index]['items'][] = $item->value;
            $assigned[] = $item->value;
        }

        foreach ($normalized as $index => $group) {
            $normalized[$index]['items'] = $this->sortGroupItems($group);
        }

        $this->menuRepository->saveGroups($normalized);

        Cache::forget(self::CACHE_KEY_GROUPS);
        $this->groups = $normalized;
    }

    private function roleAllowed(SidebarNavItem $item, UserRole $role): bool
    {
        if ($role === UserRole::SuperAdmin) {
            return true;
        }

        return $this->visibility[$item->value][$role->value] ?? true;
    }

    private function userMeetsPermission(User $user, SidebarNavItem $item): bool
    {
        if ($item === SidebarNavItem::LeaveHistory) {
            return $user->employee !== null
                && ($user->hasPermission(Permission::LeaveRequest)
                    || $user->hasPermission(Permission::LeaveViewOwn));
        }

        $permissions = $item->permissions();

        if ($permissions === []) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    private function loadPosition(): string
    {
        return Cache::remember(self::CACHE_KEY_POSITION, 3600, function () {
            return $this->menuRepository->getPosition();
        });
    }

    /** @return array<string, array<string, bool>> */
    private function loadVisibility(): array
    {
        return Cache::remember(self::CACHE_KEY_VISIBILITY, 3600, function () {
            $stored = $this->menuRepository->getVisibilityRows();

            if ($stored === []) {
                return self::defaultVisibility();
            }

            return $this->parseStoredVisibility($stored);
        });
    }

    /** @return array<string, array<string, bool>> */
    public static function defaultVisibility(): array
    {
        $matrix = [];

        foreach ([SidebarNavItem::Dashboard, ...SidebarNavItem::linkItems()] as $item) {
            $matrix[$item->value] = [];

            foreach (UserRole::cases() as $role) {
                $matrix[$item->value][$role->value] = true;
            }
        }

        return $matrix;
    }

    /** @param array<string, mixed> $stored */
    private function parseStoredVisibility(array $stored): array
    {
        $matrix = self::defaultVisibility();

        foreach ([SidebarNavItem::Dashboard, ...SidebarNavItem::linkItems()] as $item) {
            $roleValues = $stored[$item->value] ?? null;

            if (! is_array($roleValues)) {
                continue;
            }

            foreach (UserRole::cases() as $role) {
                if ($role === UserRole::SuperAdmin) {
                    $matrix[$item->value][$role->value] = true;

                    continue;
                }

                if (array_key_exists($role->value, $roleValues)) {
                    $matrix[$item->value][$role->value] = (bool) $roleValues[$role->value];
                }
            }
        }

        foreach ($stored as $key => $roleValues) {
            if (! is_string($key) || ! self::isCustomModuleKey($key) || ! is_array($roleValues)) {
                continue;
            }

            if (! isset($matrix[$key])) {
                $matrix[$key] = [];
            }

            foreach (UserRole::cases() as $role) {
                if ($role === UserRole::SuperAdmin) {
                    $matrix[$key][$role->value] = true;

                    continue;
                }

                if (array_key_exists($role->value, $roleValues)) {
                    $matrix[$key][$role->value] = (bool) $roleValues[$role->value];
                }
            }
        }

        return $matrix;
    }

    /** @return list<array{id: string, builtin: ?string, label: ?string, items: list<string>}> */
    private function loadGroups(): array
    {
        return Cache::remember(self::CACHE_KEY_GROUPS, 3600, function () {
            if (! $this->menuRepository->hasMenuStructure()) {
                return self::defaultGroups();
            }

            return $this->parseStoredGroups($this->menuRepository->getGroups());
        });
    }

    /** @return list<array{id: string, builtin: ?string, label: ?string, items: list<string>}> */
    public static function defaultGroups(): array
    {
        $groups = [
            self::defaultGroupDefinition('grp_dashboard'),
        ];

        foreach (SidebarNavItem::sections() as $section) {
            $groupId = 'grp_'.$section->value;
            $groups[] = [
                'id' => $groupId,
                'builtin' => $section->value,
                'label' => null,
                'items' => array_map(
                    fn (SidebarNavItem $item) => $item->value,
                    $section->children()
                ),
            ];
        }

        return $groups;
    }

    /** @return array{id: string, builtin: ?string, label: ?string, items: list<string>} */
    private static function defaultGroupDefinition(string $groupId): array
    {
        if ($groupId === 'grp_dashboard') {
            return [
                'id' => $groupId,
                'builtin' => 'dashboard',
                'label' => null,
                'items' => [SidebarNavItem::Dashboard->value],
            ];
        }

        $sectionKey = str_replace('grp_', '', $groupId);
        $section = SidebarNavItem::tryFrom($sectionKey);

        if ($section?->isSection()) {
            return [
                'id' => $groupId,
                'builtin' => $section->value,
                'label' => null,
                'items' => array_map(
                    fn (SidebarNavItem $item) => $item->value,
                    $section->children()
                ),
            ];
        }

        return [
            'id' => $groupId,
            'builtin' => null,
            'label' => null,
            'items' => [],
        ];
    }

    /** @param list<mixed> $stored */
    private function parseStoredGroups(array $stored): array
    {
        $groups = [];
        $assigned = [];

        foreach ($stored as $row) {
            if (! is_array($row) || ! isset($row['id']) || ! is_string($row['id'])) {
                continue;
            }

            $items = [];

            foreach ($row['items'] ?? [] as $key) {
                if (! is_string($key) || in_array($key, $assigned, true)) {
                    continue;
                }

                if (self::isCustomModuleKey($key)) {
                    $items[] = $key;
                    $assigned[] = $key;

                    continue;
                }

                $item = SidebarNavItem::tryFrom($key);

                if ($item === null || $item->isSection()) {
                    continue;
                }

                $items[] = $key;
                $assigned[] = $key;
            }

            $groups[] = [
                'id' => $row['id'],
                'builtin' => isset($row['builtin']) && is_string($row['builtin']) ? $row['builtin'] : null,
                'label' => isset($row['label']) && is_string($row['label']) ? trim($row['label']) ?: null : null,
                'items' => $items,
            ];
        }

        foreach (SidebarNavItem::defaultNavOrder() as $item) {
            if (in_array($item->value, $assigned, true)) {
                continue;
            }

            $groupId = $this->defaultGroupIdForItem($item);
            $index = $this->findGroupIndex($groups, $groupId);

            if ($index === null) {
                $groups[] = self::defaultGroupDefinition($groupId);
                $index = count($groups) - 1;
            }

            $groups[$index]['items'][] = $item->value;
            $assigned[] = $item->value;
        }

        foreach ($groups as $index => $group) {
            $groups[$index]['items'] = $this->sortGroupItems($group);
        }

        return $groups !== [] ? $groups : self::defaultGroups();
    }

    /** @param list<string> $keys */
    private function groupsFromFlatOrder(array $keys): array
    {
        $groups = [];
        $currentGroupId = null;
        $assigned = [];

        foreach ($keys as $key) {
            if (! is_string($key)) {
                continue;
            }

            $item = SidebarNavItem::tryFrom($key);

            if ($item === null || $item->isSection() || in_array($key, $assigned, true)) {
                continue;
            }

            $groupId = $this->defaultGroupIdForItem($item);

            if ($groupId !== $currentGroupId) {
                $groups[] = self::defaultGroupDefinition($groupId);
                $currentGroupId = $groupId;
            }

            $index = count($groups) - 1;
            $groups[$index]['items'][] = $key;
            $assigned[] = $key;
        }

        foreach (SidebarNavItem::defaultNavOrder() as $item) {
            if (in_array($item->value, $assigned, true)) {
                continue;
            }

            $groupId = $this->defaultGroupIdForItem($item);
            $index = $this->findGroupIndex($groups, $groupId);

            if ($index === null) {
                $groups[] = self::defaultGroupDefinition($groupId);
                $index = count($groups) - 1;
            }

            $groups[$index]['items'][] = $item->value;
        }

        foreach ($groups as $index => $group) {
            $groups[$index]['items'] = $this->sortGroupItems($group);
        }

        return $groups !== [] ? $groups : self::defaultGroups();
    }

    /**
     * @param  array{builtin?: ?string, items?: list<string>}  $group
     * @return list<string>
     */
    private function sortGroupItems(array $group): array
    {
        $items = $group['items'] ?? [];

        if ($items === []) {
            return [];
        }

        $builtin = $group['builtin'] ?? null;
        $section = is_string($builtin) ? SidebarNavItem::tryFrom($builtin) : null;

        if ($section === null || ! $section->isSection()) {
            return $items;
        }

        $canonical = array_map(
            fn (SidebarNavItem $item) => $item->value,
            $section->children()
        );

        $builtinItems = [];
        $customItems = [];

        foreach ($items as $key) {
            if (self::isCustomModuleKey($key)) {
                $customItems[] = $key;
            } else {
                $builtinItems[] = $key;
            }
        }

        usort($builtinItems, function (string $a, string $b) use ($canonical): int {
            $indexA = array_search($a, $canonical, true);
            $indexB = array_search($b, $canonical, true);

            if ($indexA === false && $indexB === false) {
                return strcmp($a, $b);
            }

            if ($indexA === false) {
                return 1;
            }

            if ($indexB === false) {
                return -1;
            }

            return $indexA <=> $indexB;
        });

        return [...$builtinItems, ...$customItems];
    }

    /** @param list<array{id: string}> $groups */
    private function findGroupIndex(array $groups, string $groupId): ?int
    {
        foreach ($groups as $index => $group) {
            if ($group['id'] === $groupId) {
                return $index;
            }
        }

        return null;
    }

    /** @return list<string> */
    public function collectModuleKeysFromGroups(array $groups): array
    {
        $keys = [];

        foreach ($groups as $group) {
            foreach ($group['items'] ?? [] as $key) {
                if (is_string($key) && self::isCustomModuleKey($key) && ! in_array($key, $keys, true)) {
                    $keys[] = $key;
                }
            }
        }

        return $keys;
    }

    /** @return array<string, array{label: string, url: string}> */
    private function loadCustomModules(): array
    {
        return Cache::remember(self::CACHE_KEY_MODULES, 3600, function () {
            return $this->menuRepository->getCustomModules();
        });
    }
}
