<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_SETTING_KEYS = [
        'sidebar_groups',
        'sidebar_visibility',
        'sidebar_custom_modules',
        'sidebar_position',
        'sidebar_order',
    ];

    public function up(): void
    {
        Schema::create('sidebar_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('position', 10)->default('left');
            $table->timestamps();
        });

        Schema::create('sidebar_menu_groups', function (Blueprint $table) {
            $table->string('id', 64)->primary();
            $table->string('builtin', 64)->nullable();
            $table->string('label', 80)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sidebar_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('group_id', 64);
            $table->string('item_key', 64);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('group_id')
                ->references('id')
                ->on('sidebar_menu_groups')
                ->cascadeOnDelete();

            $table->unique(['group_id', 'item_key']);
            $table->index('item_key');
        });

        Schema::create('sidebar_custom_modules', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->string('label', 80);
            $table->string('url', 255);
            $table->timestamps();
        });

        Schema::create('sidebar_menu_visibility', function (Blueprint $table) {
            $table->string('item_key', 64);
            $table->string('role', 64);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->primary(['item_key', 'role']);
        });

        $this->migrateLegacySettings();
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_menu_visibility');
        Schema::dropIfExists('sidebar_custom_modules');
        Schema::dropIfExists('sidebar_menu_items');
        Schema::dropIfExists('sidebar_menu_groups');
        Schema::dropIfExists('sidebar_layouts');
    }

    private function migrateLegacySettings(): void
    {
        $now = now();

        $position = DB::table('system_settings')
            ->where('key', 'sidebar_position')
            ->value('value');

        DB::table('sidebar_layouts')->insert([
            'position' => in_array($position, ['left', 'right'], true) ? $position : 'left',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $groupsJson = DB::table('system_settings')
            ->where('key', 'sidebar_groups')
            ->value('value');

        $groups = is_string($groupsJson) && $groupsJson !== ''
            ? json_decode($groupsJson, true)
            : null;

        if (! is_array($groups) || $groups === []) {
            $legacyOrder = DB::table('system_settings')
                ->where('key', 'sidebar_order')
                ->value('value');

            if (is_string($legacyOrder) && $legacyOrder !== '') {
                $decoded = json_decode($legacyOrder, true);

                if (is_array($decoded)) {
                    $groups = $this->groupsFromFlatOrder($decoded);
                }
            }
        }

        if (! is_array($groups) || $groups === []) {
            $groups = $this->defaultGroups();
        }

        foreach ($groups as $sortOrder => $group) {
            if (! is_array($group) || ! isset($group['id']) || ! is_string($group['id'])) {
                continue;
            }

            DB::table('sidebar_menu_groups')->insert([
                'id' => $group['id'],
                'builtin' => isset($group['builtin']) && is_string($group['builtin']) ? $group['builtin'] : null,
                'label' => isset($group['label']) && is_string($group['label']) ? trim($group['label']) ?: null : null,
                'sort_order' => $sortOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($group['items'] ?? [] as $itemSort => $itemKey) {
                if (! is_string($itemKey)) {
                    continue;
                }

                DB::table('sidebar_menu_items')->insert([
                    'group_id' => $group['id'],
                    'item_key' => $itemKey,
                    'sort_order' => $itemSort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $modulesJson = DB::table('system_settings')
            ->where('key', 'sidebar_custom_modules')
            ->value('value');

        if (is_string($modulesJson) && $modulesJson !== '') {
            $modules = json_decode($modulesJson, true);

            if (is_array($modules)) {
                foreach ($modules as $key => $row) {
                    if (! is_string($key) || ! str_starts_with($key, 'mod_') || ! is_array($row)) {
                        continue;
                    }

                    $label = trim((string) ($row['label'] ?? ''));
                    $url = trim((string) ($row['url'] ?? ''));

                    if ($label === '' || $url === '') {
                        continue;
                    }

                    DB::table('sidebar_custom_modules')->insert([
                        'key' => $key,
                        'label' => $label,
                        'url' => $url,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        $visibilityJson = DB::table('system_settings')
            ->where('key', 'sidebar_visibility')
            ->value('value');

        if (is_string($visibilityJson) && $visibilityJson !== '') {
            $stored = json_decode($visibilityJson, true);

            if (is_array($stored)) {
                foreach ($stored as $itemKey => $roleValues) {
                    if (! is_string($itemKey) || ! is_array($roleValues)) {
                        continue;
                    }

                    foreach ($roleValues as $role => $visible) {
                        if (! is_string($role)) {
                            continue;
                        }

                        DB::table('sidebar_menu_visibility')->insert([
                            'item_key' => $itemKey,
                            'role' => $role,
                            'is_visible' => (bool) $visible,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }

        DB::table('system_settings')
            ->whereIn('key', self::LEGACY_SETTING_KEYS)
            ->delete();
    }

    /** @return list<array{id: string, builtin: ?string, label: ?string, items: list<string>}> */
    private function defaultGroups(): array
    {
        return [
            [
                'id' => 'grp_dashboard',
                'builtin' => 'dashboard',
                'label' => null,
                'items' => ['dashboard'],
            ],
            [
                'id' => 'grp_section_attendance',
                'builtin' => 'section_attendance',
                'label' => null,
                'items' => ['attendance_scan', 'attendance_history', 'attendance_manage', 'fingerprint_devices'],
            ],
            [
                'id' => 'grp_section_leave',
                'builtin' => 'section_leave',
                'label' => null,
                'items' => ['leave_history', 'leave_create', 'leave_approval'],
            ],
            [
                'id' => 'grp_section_payroll',
                'builtin' => 'section_payroll',
                'label' => null,
                'items' => ['payroll'],
            ],
            [
                'id' => 'grp_section_master',
                'builtin' => 'section_master',
                'label' => null,
                'items' => ['branches', 'departments', 'positions', 'employees', 'shift_templates', 'employee_shifts', 'holidays'],
            ],
            [
                'id' => 'grp_section_system',
                'builtin' => 'section_system',
                'label' => null,
                'items' => ['reports', 'users', 'roles', 'settings'],
            ],
        ];
    }

    /** @param list<string> $keys @return list<array{id: string, builtin: ?string, label: ?string, items: list<string>}> */
    private function groupsFromFlatOrder(array $keys): array
    {
        $sectionMap = [
            'dashboard' => ['id' => 'grp_dashboard', 'builtin' => 'dashboard'],
            'section_attendance' => ['id' => 'grp_section_attendance', 'builtin' => 'section_attendance'],
            'section_leave' => ['id' => 'grp_section_leave', 'builtin' => 'section_leave'],
            'section_payroll' => ['id' => 'grp_section_payroll', 'builtin' => 'section_payroll'],
            'section_master' => ['id' => 'grp_section_master', 'builtin' => 'section_master'],
            'section_system' => ['id' => 'grp_section_system', 'builtin' => 'section_system'],
        ];

        $parentMap = [
            'dashboard' => 'grp_dashboard',
            'attendance_scan' => 'grp_section_attendance',
            'attendance_history' => 'grp_section_attendance',
            'attendance_manage' => 'grp_section_attendance',
            'fingerprint_devices' => 'grp_section_attendance',
            'leave_history' => 'grp_section_leave',
            'leave_create' => 'grp_section_leave',
            'leave_approval' => 'grp_section_leave',
            'payroll' => 'grp_section_payroll',
            'branches' => 'grp_section_master',
            'departments' => 'grp_section_master',
            'positions' => 'grp_section_master',
            'employees' => 'grp_section_master',
            'shift_templates' => 'grp_section_master',
            'employee_shifts' => 'grp_section_master',
            'holidays' => 'grp_section_master',
            'reports' => 'grp_section_system',
            'users' => 'grp_section_system',
            'roles' => 'grp_section_system',
            'settings' => 'grp_section_system',
        ];

        $groups = [];
        $groupIndex = [];

        foreach ($keys as $key) {
            if (! is_string($key) || str_starts_with($key, 'section_')) {
                continue;
            }

            $groupId = $parentMap[$key] ?? 'grp_custom_misc';

            if (! isset($groupIndex[$groupId])) {
                $meta = $sectionMap[str_replace('grp_', '', $groupId)] ?? null;

                $groups[] = [
                    'id' => $groupId,
                    'builtin' => $meta['builtin'] ?? null,
                    'label' => null,
                    'items' => [],
                ];

                $groupIndex[$groupId] = count($groups) - 1;
            }

            $groups[$groupIndex[$groupId]]['items'][] = $key;
        }

        return $groups;
    }
};
