<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class UserDefaultPasswordService
{
    private const CACHE_KEY = 'user_default_password_settings_v1';

    private const SETTING_MODE = 'user_default_password_mode';

    private const SETTING_CUSTOM = 'user_default_password_custom';

    /** @return array{mode: string, custom: string} */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $mode = (string) SystemSetting::getValue(self::SETTING_MODE, 'employee_number');

            if (! in_array($mode, ['employee_number', 'custom'], true)) {
                $mode = 'employee_number';
            }

            return [
                'mode' => $mode,
                'custom' => (string) SystemSetting::getValue(self::SETTING_CUSTOM, ''),
            ];
        });
    }

    public function mode(): string
    {
        return $this->all()['mode'];
    }

    public function usesEmployeeNumber(): bool
    {
        return $this->mode() === 'employee_number';
    }

    public function save(string $mode, ?string $customPassword = null): void
    {
        if (! in_array($mode, ['employee_number', 'custom'], true)) {
            throw new \InvalidArgumentException(__('pages.settings.user_password_mode_invalid'));
        }

        $custom = trim((string) $customPassword);

        if ($mode === 'custom' && strlen($custom) < 6) {
            throw new \InvalidArgumentException(__('pages.settings.user_password_custom_required'));
        }

        SystemSetting::setValue(self::SETTING_MODE, $mode, 'Mode password default pengguna');
        SystemSetting::setValue(
            self::SETTING_CUSTOM,
            $mode === 'custom' ? $custom : '',
            'Password default pengguna (kustom)'
        );

        Cache::forget(self::CACHE_KEY);
    }

    public function resolve(?Employee $employee = null, ?string $manualPassword = null): string
    {
        $manual = trim((string) $manualPassword);

        if ($manual !== '') {
            return $manual;
        }

        if ($this->usesEmployeeNumber()) {
            return $this->fromEmployeeNumber($employee);
        }

        $custom = trim($this->all()['custom']);

        if ($custom !== '') {
            return $custom;
        }

        return $this->fromEmployeeNumber($employee);
    }

    private function fromEmployeeNumber(?Employee $employee): string
    {
        $password = preg_replace('/\s+/', '', (string) ($employee?->employee_number ?? ''));

        if ($password === '') {
            $password = 'pegawai';
        }

        if (strlen($password) < 6) {
            $password = str_pad($password, 6, '0', STR_PAD_LEFT);
        }

        return $password;
    }
}
