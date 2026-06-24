<?php

namespace App\Services;

use App\Models\AppBranding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AppBrandingService
{
    private const CACHE_KEY = 'app_branding_settings_v2';

    /** @return array{name: string, logo_path: string} */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            $branding = AppBranding::current();

            return [
                'name' => trim((string) $branding->app_name),
                'logo_path' => trim((string) ($branding->logo_path ?? '')),
            ];
        });
    }

    public function name(): string
    {
        $name = $this->all()['name'];

        return $name !== '' ? $name : (string) __('app.name');
    }

    public function hasLogo(): bool
    {
        $path = $this->all()['logo_path'];

        return $path !== '' && Storage::disk('public')->exists($path);
    }

    public function logoUrl(): ?string
    {
        if (! $this->hasLogo()) {
            return null;
        }

        $branding = AppBranding::current();

        return route('branding.logo', [
            'v' => $branding->updated_at?->getTimestamp() ?? 0,
        ], false);
    }

    public function save(string $name, ?UploadedFile $logo = null, bool $removeLogo = false): void
    {
        $trimmedName = trim($name);

        if ($trimmedName === '') {
            throw new \InvalidArgumentException(__('pages.settings.branding_name_required'));
        }

        $branding = AppBranding::current();
        $branding->app_name = $trimmedName;

        if ($removeLogo) {
            $this->deleteStoredLogo($branding->logo_path);
            $branding->logo_path = null;
        } elseif ($logo !== null) {
            $this->deleteStoredLogo($branding->logo_path);
            $extension = strtolower($logo->getClientOriginalExtension() ?: 'png');
            $branding->logo_path = $logo->storeAs('branding', 'app-logo.'.$extension, 'public');
        }

        $branding->save();

        Cache::forget(self::CACHE_KEY);
    }

    private function deleteStoredLogo(?string $path): void
    {
        $path = trim((string) $path);

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
