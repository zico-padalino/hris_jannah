<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Enums\SidebarNavItem;
use App\Enums\UserRole;
use App\Models\SystemSetting;
use App\Services\AppBrandingService;
use App\Services\AttendanceMethodSettingsService;
use App\Services\ModuleMaintenanceService;
use App\Services\SidebarService;
use App\Services\UserDefaultPasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class SettingsController extends WebController
{
    public function index(Request $request, SidebarService $sidebarService, AttendanceMethodSettingsService $attendanceMethods, ModuleMaintenanceService $moduleMaintenance, UserDefaultPasswordService $defaultPasswords, AppBrandingService $branding): View
    {
        $this->authorizePermission($request, Permission::SettingsManage);

        $settings = [
            'face_match_threshold' => SystemSetting::getValue('face_match_threshold', config('attendance.face_match_threshold')),
            'location_buffer_meters' => SystemSetting::getValue('location_buffer_meters', config('attendance.location_buffer_meters')),
            'sidebar_position' => $sidebarService->position(),
            'attendance_methods' => $attendanceMethods->all(),
            'maintenance_modules' => $moduleMaintenance->enabledModules(),
            'maintenance_message' => $moduleMaintenance->message() ?? '',
            'user_default_password' => $defaultPasswords->all(),
            'branding' => $branding->all(),
        ];

        $roles = UserRole::cases();
        $sidebarGroups = $sidebarService->groups();
        $sidebarVisibility = $sidebarService->visibilityMatrix();
        $customModules = $sidebarService->customModules();
        $builtinModuleOptions = $sidebarService->builtinModuleOptions();
        $maintenanceModuleOptions = $moduleMaintenance->maintainableModuleOptions();

        return view('settings.index', compact(
            'settings',
            'roles',
            'sidebarGroups',
            'sidebarVisibility',
            'sidebarService',
            'customModules',
            'builtinModuleOptions',
            'maintenanceModuleOptions',
        ));
    }

    public function update(Request $request, SidebarService $sidebarService, AttendanceMethodSettingsService $attendanceMethods, ModuleMaintenanceService $moduleMaintenance, UserDefaultPasswordService $defaultPasswords, AppBrandingService $branding): RedirectResponse
    {
        $this->authorizePermission($request, Permission::SettingsManage);

        $validBuiltinItems = array_map(
            fn (SidebarNavItem $item) => $item->value,
            [SidebarNavItem::Dashboard, ...SidebarNavItem::linkItems()]
        );

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'app_logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_app_logo' => ['nullable', 'boolean'],
            'face_match_threshold' => ['required', 'numeric', 'min:0.1', 'max:1'],
            'location_buffer_meters' => ['required', 'integer', 'min:0', 'max:500'],
            'attendance_method_fingerprint' => ['nullable', 'boolean'],
            'attendance_method_photo' => ['nullable', 'boolean'],
            'attendance_method_gps' => ['nullable', 'boolean'],
            'sidebar_position' => ['required', 'in:left,right'],
            'sidebar_visibility' => ['nullable', 'array'],
            'sidebar_visibility.*' => ['nullable', 'array'],
            'sidebar_visibility.*.*' => ['string', 'max:64'],
            'sidebar_groups_order' => ['required', 'array', 'min:1'],
            'sidebar_groups_order.*' => ['string', 'max:64'],
            'sidebar_groups' => ['required', 'array'],
            'sidebar_groups.*.builtin' => ['nullable', 'string', 'max:64'],
            'sidebar_groups.*.label' => ['nullable', 'string', 'max:80'],
            'sidebar_groups.*.items' => ['nullable', 'array'],
            'sidebar_groups.*.items.*' => ['string', 'max:64'],
            'sidebar_modules' => ['nullable', 'array'],
            'sidebar_modules.*.label' => ['nullable', 'string', 'max:80'],
            'sidebar_modules.*.url' => ['nullable', 'string', 'max:255'],
            'maintenance_modules' => ['nullable', 'array'],
            'maintenance_modules.*' => ['string', 'max:64'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'user_default_password_mode' => ['required', 'in:employee_number,custom'],
            'user_default_password_custom' => ['nullable', 'string', 'min:6', 'max:64'],
        ]);

        $validator = \Illuminate\Support\Facades\Validator::make($data, []);
        $this->validateSidebarItems($validator, $data, $validBuiltinItems);

        if ($validator->errors()->isNotEmpty()) {
            return back()->withErrors($validator)->withInput();
        }

        SystemSetting::setValue('face_match_threshold', $data['face_match_threshold'], 'Ambang kecocokan wajah');
        SystemSetting::setValue('location_buffer_meters', $data['location_buffer_meters'], 'Buffer radius lokasi (meter)');

        try {
            $branding->save(
                $data['app_name'],
                $request->file('app_logo'),
                $request->boolean('remove_app_logo') && ! $request->hasFile('app_logo')
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['app_name' => $e->getMessage()])->withInput();
        }

        try {
            $defaultPasswords->save(
                $data['user_default_password_mode'],
                $data['user_default_password_custom'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['user_default_password_custom' => $e->getMessage()])->withInput();
        }

        try {
            $attendanceMethods->save([
                'fingerprint' => $request->boolean('attendance_method_fingerprint'),
                'photo' => $request->boolean('attendance_method_photo'),
                'gps' => $request->boolean('attendance_method_gps'),
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['attendance_method_fingerprint' => $e->getMessage()])->withInput();
        }

        $sidebarService->savePosition($data['sidebar_position']);
        $sidebarService->saveGroups(
            $data['sidebar_groups_order'],
            $data['sidebar_groups']
        );

        $usedModuleKeys = $sidebarService->collectModuleKeysFromGroups($sidebarService->groups());
        $sidebarService->saveCustomModules($data['sidebar_modules'] ?? [], $usedModuleKeys);
        $sidebarService->saveVisibility($data['sidebar_visibility'] ?? []);

        $moduleMaintenance->save(
            $data['maintenance_modules'] ?? [],
            $data['maintenance_message'] ?? null
        );

        return back()->with('success', __('messages.settings_saved'));
    }

    /** @param  array<string, mixed>  $data */
    private function validateSidebarItems(Validator $validator, array $data, array $validBuiltinItems): void
    {
        foreach ($data['sidebar_groups'] ?? [] as $groupId => $group) {
            if (! is_array($group)) {
                continue;
            }

            foreach ($group['items'] ?? [] as $index => $key) {
                if (! is_string($key)) {
                    continue;
                }

                if (SidebarService::isCustomModuleKey($key)) {
                    continue;
                }

                if (! in_array($key, $validBuiltinItems, true)) {
                    $validator->errors()->add(
                        "sidebar_groups.{$groupId}.items.{$index}",
                        __('pages.settings.sidebar_invalid_module')
                    );
                }
            }
        }

        foreach ($data['sidebar_visibility'] ?? [] as $role => $keys) {
            if (! is_array($keys)) {
                continue;
            }

            foreach ($keys as $index => $key) {
                if (! is_string($key)) {
                    continue;
                }

                if (SidebarService::isCustomModuleKey($key)) {
                    continue;
                }

                if (! in_array($key, $validBuiltinItems, true)) {
                    $validator->errors()->add(
                        "sidebar_visibility.{$role}.{$index}",
                        __('pages.settings.sidebar_invalid_module')
                    );
                }
            }
        }
    }
}
