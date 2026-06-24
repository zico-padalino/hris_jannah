<?php

namespace App\Repositories;

use App\Models\SidebarCustomModule;
use App\Models\SidebarLayout;
use App\Models\SidebarMenuGroup;
use App\Models\SidebarMenuItem;
use Illuminate\Support\Facades\DB;

class SidebarMenuRepository
{
    public function getPosition(): string
    {
        $position = SidebarLayout::query()->value('position');

        return in_array($position, ['left', 'right'], true) ? $position : 'left';
    }

    public function savePosition(string $position): void
    {
        $layout = SidebarLayout::query()->first();

        if ($layout === null) {
            SidebarLayout::query()->create(['position' => $position]);

            return;
        }

        $layout->update(['position' => $position]);
    }

    /** @return list<array{id: string, builtin: ?string, label: ?string, items: list<string>}> */
    public function getGroups(): array
    {
        $groups = SidebarMenuGroup::query()
            ->with('items')
            ->orderBy('sort_order')
            ->get();

        if ($groups->isEmpty()) {
            return [];
        }

        $result = [];

        foreach ($groups as $group) {
            $result[] = [
                'id' => $group->id,
                'builtin' => $group->builtin,
                'label' => $group->label,
                'items' => $group->items->pluck('item_key')->all(),
            ];
        }

        return $result;
    }

    /**
     * @param  list<array{id: string, builtin: ?string, label: ?string, items: list<string>}>  $groups
     */
    public function saveGroups(array $groups): void
    {
        DB::transaction(function () use ($groups): void {
            $groupIds = array_map(fn (array $group) => $group['id'], $groups);

            SidebarMenuGroup::query()
                ->whereNotIn('id', $groupIds)
                ->delete();

            foreach ($groups as $sortOrder => $group) {
                SidebarMenuGroup::query()->updateOrCreate(
                    ['id' => $group['id']],
                    [
                        'builtin' => $group['builtin'],
                        'label' => $group['label'],
                        'sort_order' => $sortOrder,
                    ]
                );

                SidebarMenuItem::query()
                    ->where('group_id', $group['id'])
                    ->delete();

                foreach ($group['items'] as $itemSort => $itemKey) {
                    SidebarMenuItem::query()->create([
                        'group_id' => $group['id'],
                        'item_key' => $itemKey,
                        'sort_order' => $itemSort,
                    ]);
                }
            }
        });
    }

    /** @return array<string, array{label: string, url: string}> */
    public function getCustomModules(): array
    {
        $modules = [];

        foreach (SidebarCustomModule::query()->get() as $module) {
            $modules[$module->key] = [
                'label' => $module->label,
                'url' => $module->url,
            ];
        }

        return $modules;
    }

    /** @param array<string, array{label: string, url: string}> $modules */
    public function saveCustomModules(array $modules): void
    {
        DB::transaction(function () use ($modules): void {
            $keys = array_keys($modules);

            SidebarCustomModule::query()
                ->whereNotIn('key', $keys)
                ->delete();

            foreach ($modules as $key => $row) {
                SidebarCustomModule::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'label' => $row['label'],
                        'url' => $row['url'],
                    ]
                );
            }
        });
    }

    /** @return array<string, array<string, bool>> */
    public function getVisibilityRows(): array
    {
        $matrix = [];

        foreach (DB::table('sidebar_menu_visibility')->get() as $row) {
            $matrix[$row->item_key][$row->role] = (bool) $row->is_visible;
        }

        return $matrix;
    }

    /** @param array<string, array<string, bool>> $matrix */
    public function saveVisibility(array $matrix): void
    {
        DB::transaction(function () use ($matrix): void {
            DB::table('sidebar_menu_visibility')->delete();

            $now = now();
            $rows = [];

            foreach ($matrix as $itemKey => $roleValues) {
                foreach ($roleValues as $role => $visible) {
                    $rows[] = [
                        'item_key' => $itemKey,
                        'role' => $role,
                        'is_visible' => $visible,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($rows !== []) {
                foreach (array_chunk($rows, 100) as $chunk) {
                    DB::table('sidebar_menu_visibility')->insert($chunk);
                }
            }
        });
    }

    public function hasMenuStructure(): bool
    {
        return SidebarMenuGroup::query()->exists();
    }
}
