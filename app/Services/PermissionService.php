<?php

namespace App\Services;

use App\Enums\Permission;
use App\Enums\UserRole;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    private const CACHE_KEY = 'role_permissions_matrix_v2';

    /** @var array<string, list<Permission>> */
    private array $rolePermissions;

    public function __construct()
    {
        $this->rolePermissions = $this->loadMatrix();
    }

    public function userHas(User $user, Permission $permission): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return in_array($permission, $this->forRole($user->role), true);
    }

    /** @return list<Permission> */
    public function forRole(UserRole $role): array
    {
        if ($role === UserRole::SuperAdmin) {
            return Permission::cases();
        }

        return $this->rolePermissions[$role->value] ?? [];
    }

    /** @return array<string, list<Permission>> */
    public function matrix(): array
    {
        return $this->rolePermissions;
    }

    /** @param array<string, list<string>> $input */
    public function saveMatrix(array $input): void
    {
        $matrix = [];

        foreach (UserRole::cases() as $role) {
            if ($role === UserRole::SuperAdmin) {
                $matrix[$role->value] = array_map(
                    fn (Permission $p) => $p->value,
                    Permission::cases()
                );

                continue;
            }

            $permissions = $input[$role->value] ?? [];
            $valid = [];

            foreach ($permissions as $permissionValue) {
                $permission = Permission::tryFrom($permissionValue);

                if ($permission !== null) {
                    $valid[] = $permission->value;
                }
            }

            $matrix[$role->value] = array_values(array_unique($valid));
        }

        SystemSetting::setValue(
            'role_permissions',
            json_encode($matrix, JSON_THROW_ON_ERROR),
            'Matriks hak akses per role'
        );

        Cache::forget(self::CACHE_KEY);
        $this->rolePermissions = $this->loadMatrix();
    }

    /** @param list<string> $permissionValues */
    public function saveRolePermissions(UserRole $role, array $permissionValues): void
    {
        if ($role === UserRole::SuperAdmin) {
            return;
        }

        $input = [];

        foreach (UserRole::cases() as $case) {
            $input[$case->value] = array_map(
                fn (Permission $permission) => $permission->value,
                $this->forRole($case)
            );
        }

        $valid = [];

        foreach ($permissionValues as $permissionValue) {
            $permission = Permission::tryFrom($permissionValue);

            if ($permission !== null) {
                $valid[] = $permission->value;
            }
        }

        $input[$role->value] = array_values(array_unique($valid));

        $this->saveMatrix($input);
    }

    /** @return array<string, string> */
    public function roleDescriptions(): array
    {
        $descriptions = [];

        foreach (UserRole::cases() as $role) {
            $descriptions[$role->value] = $role->defaultDescription();
        }

        $stored = SystemSetting::getValue('role_descriptions');

        if (! is_string($stored) || $stored === '') {
            return $descriptions;
        }

        $decoded = json_decode($stored, true);

        if (! is_array($decoded)) {
            return $descriptions;
        }

        foreach ($decoded as $roleValue => $description) {
            if (! is_string($roleValue) || ! is_string($description)) {
                continue;
            }

            if (! array_key_exists($roleValue, $descriptions)) {
                continue;
            }

            $trimmed = trim($description);

            if ($trimmed !== '') {
                $descriptions[$roleValue] = $trimmed;
            }
        }

        return $descriptions;
    }

    public function descriptionForRole(UserRole $role): string
    {
        return $this->roleDescriptions()[$role->value] ?? $role->defaultDescription();
    }

    public function saveRoleDescription(UserRole $role, string $description): void
    {
        $descriptions = $this->roleDescriptions();
        $descriptions[$role->value] = trim($description);

        SystemSetting::setValue(
            'role_descriptions',
            json_encode($descriptions, JSON_THROW_ON_ERROR),
            'Deskripsi role pengguna'
        );
    }

    /** @return array<string, list<Permission>> */
    private function loadMatrix(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $stored = SystemSetting::getValue('role_permissions');

            if (is_string($stored) && $stored !== '') {
                $decoded = json_decode($stored, true);

                if (is_array($decoded)) {
                    return $this->parseStoredMatrix($decoded);
                }
            }

            return self::defaultMatrix();
        });
    }

    /** @return array<string, list<Permission>> */
    public static function defaultMatrix(): array
    {
        return [
            UserRole::SuperAdmin->value => Permission::cases(),
            UserRole::Hr->value => array_values(array_filter(
                Permission::cases(),
                fn (Permission $p) => ! in_array($p, [Permission::SettingsManage], true)
            )),
            UserRole::BranchAdmin->value => [
                Permission::DashboardView,
                Permission::AttendanceScan,
                Permission::AttendanceViewAll,
                Permission::AttendanceManage,
                Permission::DepartmentsManage,
                Permission::PositionsManage,
                Permission::EmployeesManage,
                Permission::FacesEnroll,
                Permission::ShiftsManage,
                Permission::HolidaysManage,
                Permission::LeaveApprove,
                Permission::PayrollManage,
                Permission::ReportsView,
                Permission::FingerprintManage,
                Permission::AnnouncementsManage,
            ],
            UserRole::Employee->value => [
                Permission::DashboardView,
                Permission::AttendanceScan,
                Permission::AttendanceViewOwn,
                Permission::LeaveRequest,
                Permission::LeaveViewOwn,
                Permission::PayrollViewOwn,
            ],
        ];
    }

    /** @param array<string, mixed> $stored */
    private function parseStoredMatrix(array $stored): array
    {
        $matrix = self::defaultMatrix();

        foreach (UserRole::cases() as $role) {
            if ($role === UserRole::SuperAdmin) {
                $matrix[$role->value] = Permission::cases();

                continue;
            }

            $permissions = $stored[$role->value] ?? null;

            if (! is_array($permissions)) {
                continue;
            }

            $parsed = [];

            foreach ($permissions as $permissionValue) {
                if (! is_string($permissionValue)) {
                    continue;
                }

                $permission = Permission::tryFrom($permissionValue);

                if ($permission !== null) {
                    $parsed[] = $permission;
                }
            }

            $matrix[$role->value] = $this->mergeDefaultPermissions($role, $parsed);
        }

        return $matrix;
    }

    /**
     * Tambahkan permission baru dari default matrix jika belum ada di data tersimpan.
     *
     * @param  list<Permission>  $assigned
     * @return list<Permission>
     */
    private function mergeDefaultPermissions(UserRole $role, array $assigned): array
    {
        $defaults = self::defaultMatrix()[$role->value] ?? [];

        foreach ($defaults as $permission) {
            if (! in_array($permission, $assigned, true)) {
                $assigned[] = $permission;
            }
        }

        return $assigned;
    }
}
