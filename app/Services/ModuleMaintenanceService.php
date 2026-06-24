<?php

namespace App\Services;

use App\Enums\SidebarNavItem;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class ModuleMaintenanceService
{
    private const CACHE_KEY = 'module_maintenance_v1';

    private const SETTING_MODULES = 'module_maintenance_modules';

    private const SETTING_MESSAGE = 'module_maintenance_message';

    /** @return list<string> */
    public function enabledModules(): array
    {
        return $this->all()['modules'];
    }

    public function message(): ?string
    {
        $message = trim((string) ($this->all()['message'] ?? ''));

        return $message !== '' ? $message : null;
    }

    public function isInMaintenance(string $moduleKey): bool
    {
        return in_array($moduleKey, $this->enabledModules(), true);
    }

    public function isRouteInMaintenance(?string $routeName): bool
    {
        $module = SidebarNavItem::fromRouteName($routeName);

        if ($module === null || $module === SidebarNavItem::Settings) {
            return false;
        }

        return $this->isInMaintenance($module->value);
    }

    /** @return list<array{value: string, label: string, section: ?string}> */
    public function maintainableModuleOptions(): array
    {
        $options = [];

        $dashboard = SidebarNavItem::Dashboard;
        $options[] = [
            'value' => $dashboard->value,
            'label' => __($dashboard->navLabelKey()),
            'section' => null,
        ];

        foreach (SidebarNavItem::sections() as $section) {
            foreach ($section->children() as $item) {
                if ($item === SidebarNavItem::Settings) {
                    continue;
                }

                $options[] = [
                    'value' => $item->value,
                    'label' => __($item->navLabelKey()),
                    'section' => __($section->navLabelKey()),
                ];
            }
        }

        return $options;
    }

    /** @param list<string> $moduleKeys */
    public function save(array $moduleKeys, ?string $message = null): void
    {
        $validKeys = array_map(
            fn (SidebarNavItem $item) => $item->value,
            [SidebarNavItem::Dashboard, ...SidebarNavItem::linkItems()]
        );

        $filtered = array_values(array_unique(array_filter(
            $moduleKeys,
            fn (string $key) => in_array($key, $validKeys, true) && $key !== SidebarNavItem::Settings->value
        )));

        SystemSetting::setValue(
            self::SETTING_MODULES,
            json_encode($filtered, JSON_THROW_ON_ERROR),
            'Modul dalam maintenance'
        );

        SystemSetting::setValue(
            self::SETTING_MESSAGE,
            trim((string) $message),
            'Pesan maintenance modul'
        );

        Cache::forget(self::CACHE_KEY);
    }

    /** @return array{modules: list<string>, message: ?string} */
    private function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $raw = SystemSetting::getValue(self::SETTING_MODULES, '[]');
            $modules = [];

            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);

                if (is_array($decoded)) {
                    $modules = array_values(array_filter(
                        $decoded,
                        fn ($key) => is_string($key) && $key !== ''
                    ));
                }
            }

            $message = SystemSetting::getValue(self::SETTING_MESSAGE);

            return [
                'modules' => $modules,
                'message' => is_string($message) ? $message : null,
            ];
        });
    }
}
