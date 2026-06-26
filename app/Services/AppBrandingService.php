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

    /**
     * @return array{first: string, second: string|null}
     */
    public function nameLines(): array
    {
        $parts = preg_split('/\s+/', $this->name(), 2, PREG_SPLIT_NO_EMPTY);

        return [
            'first' => $parts[0] ?? $this->name(),
            'second' => isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null,
        ];
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
            $this->normalizeUploadedLogo($branding->logo_path);
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

    /**
     * Strip baked-in checkerboard / light backgrounds from PNG logos.
     * Common when a design tool's transparency grid is saved as part of the image.
     */
    private function normalizeUploadedLogo(string $storedPath): void
    {
        if (! str_ends_with(strtolower($storedPath), '.png') || ! extension_loaded('gd')) {
            return;
        }

        $fullPath = Storage::disk('public')->path($storedPath);
        $image = @imagecreatefrompng($fullPath);

        if ($image === false) {
            return;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $red = ($color >> 16) & 0xFF;
                $green = ($color >> 8) & 0xFF;
                $blue = $color & 0xFF;

                if ($this->isBakedLogoBackground($red, $green, $blue)) {
                    imagesetpixel($image, $x, $y, $transparent);
                }
            }
        }

        imagepng($image, $fullPath);
        imagedestroy($image);
    }

    private function isBakedLogoBackground(int $red, int $green, int $blue): bool
    {
        if ($red >= 245 && $green >= 245 && $blue >= 245) {
            return true;
        }

        $channelSpread = max(abs($red - $green), abs($green - $blue), abs($red - $blue));

        return $channelSpread <= 8 && $red >= 168 && $red <= 238;
    }
}
