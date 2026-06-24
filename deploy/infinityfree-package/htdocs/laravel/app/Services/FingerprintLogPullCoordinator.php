<?php

namespace App\Services;

use App\Models\FingerprintDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FingerprintLogPullCoordinator
{
    public function __construct(
        private readonly ZktecoTcpPullService $tcpPullService,
    ) {}

    public function maybePullOnDeviceActivity(FingerprintDevice $device): void
    {
        if (! $this->shouldPullOnActivity()) {
            return;
        }

        if (! $device->is_active || ! $device->ip_address || $device->serial_number === 'unknown') {
            return;
        }

        $interval = max(10, (int) config('attendance.fingerprint_auto_pull_seconds', 30));
        $cacheKey = 'fingerprint_pull_throttle:'.$device->id;

        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addSeconds($interval));

        $deviceId = $device->id;

        app()->terminating(function () use ($deviceId): void {
            $device = FingerprintDevice::query()->find($deviceId);

            if ($device === null || ! $device->is_active || ! $device->ip_address) {
                return;
            }

            try {
                $this->tcpPullService->pullFromDevice($device);
            } catch (\Throwable $exception) {
                Log::warning('Fingerprint TCP pull on ADMS activity failed', [
                    'device' => $device->serial_number,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }

    public function modeLabel(): string
    {
        return match (config('attendance.fingerprint_log_mode', 'tcp')) {
            'adms' => 'ADMS push',
            'hybrid' => 'Hybrid (ADMS + TCP)',
            'scheduled' => 'TCP terjadwal (schedule:work)',
            default => 'TCP port 4370',
        };
    }

    private function shouldPullOnActivity(): bool
    {
        return config('attendance.fingerprint_log_mode') === 'hybrid';
    }
}
