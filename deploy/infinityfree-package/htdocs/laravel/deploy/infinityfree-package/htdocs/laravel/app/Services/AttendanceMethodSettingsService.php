<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class AttendanceMethodSettingsService
{
    private const CACHE_KEY = 'attendance_method_settings_v1';

    /** @return array{fingerprint: bool, photo: bool, gps: bool} */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return [
                'fingerprint' => $this->readBool('attendance_method_fingerprint', true),
                'photo' => $this->readBool('attendance_method_photo', true),
                'gps' => $this->readBool('attendance_method_gps', false),
            ];
        });
    }

    public function fingerprintEnabled(): bool
    {
        return $this->all()['fingerprint'];
    }

    public function photoEnabled(): bool
    {
        return $this->all()['photo'];
    }

    public function gpsEnabled(): bool
    {
        return $this->all()['gps'];
    }

    public function hasAnyWebMethod(): bool
    {
        return $this->photoEnabled() || $this->gpsEnabled();
    }

    /** @param array{fingerprint?: bool, photo?: bool, gps?: bool} $input */
    public function save(array $input): void
    {
        $fingerprint = (bool) ($input['fingerprint'] ?? false);
        $photo = (bool) ($input['photo'] ?? false);
        $gps = (bool) ($input['gps'] ?? false);

        if (! $fingerprint && ! $photo && ! $gps) {
            throw new \InvalidArgumentException(__('pages.settings.attendance_method_required'));
        }

        SystemSetting::setValue('attendance_method_fingerprint', $fingerprint ? '1' : '0', 'Metode absensi: fingerprint');
        SystemSetting::setValue('attendance_method_photo', $photo ? '1' : '0', 'Metode absensi: foto/wajah');
        SystemSetting::setValue('attendance_method_gps', $gps ? '1' : '0', 'Metode absensi: GPS lokasi');

        Cache::forget(self::CACHE_KEY);
    }

    private function readBool(string $key, bool $default): bool
    {
        $value = SystemSetting::getValue($key);

        if ($value === null) {
            return $default;
        }

        return in_array((string) $value, ['1', 'true', 'yes', 'on'], true);
    }
}
