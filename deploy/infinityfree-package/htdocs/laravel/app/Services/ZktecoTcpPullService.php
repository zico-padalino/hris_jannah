<?php

namespace App\Services;

use App\Models\FingerprintDevice;
use App\Models\FingerprintLog;
use Carbon\Carbon;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use Illuminate\Support\Facades\Log;
use Throwable;

class ZktecoTcpPullService
{
    public function __construct(
        private readonly FingerprintAttendanceService $fingerprintAttendanceService,
    ) {}

    /**
     * @return array{processed: int, skipped: int, failed: int, total: int, message: string}
     */
    public function pullFromDevice(FingerprintDevice $device): array
    {
        if (! $device->ip_address) {
            return $this->result(0, 0, 0, 0, 'IP mesin belum diketahui.');
        }

        $zk = new ZKTeco($device->ip_address, 4370, true, 15);

        try {
            if (! $zk->connect()) {
                return $this->result(0, 0, 0, 0, 'Gagal koneksi TCP ke mesin (port 4370). Pastikan mesin tidak sedang dipakai software lain.');
            }

            $records = $zk->getAttendances();
            $zk->disconnect();

            if ($records === []) {
                return $this->result(0, 0, 0, 0, 'Tidak ada data absensi di mesin atau gagal membaca log.');
            }

            $existingKeys = $this->existingLogKeys($device);
            $lines = [];
            $skipped = 0;

            foreach ($records as $record) {
                $pin = (string) ($record['user_id'] ?? '');
                $timestamp = $record['record_time'] ?? null;
                $state = (int) ($record['state'] ?? 0);

                if ($pin === '' || $timestamp === null) {
                    continue;
                }

                $punchedAt = Carbon::parse($timestamp, config('app.timezone'))->format('Y-m-d H:i:s');
                $key = $pin.'|'.$punchedAt.'|'.$state;

                if (isset($existingKeys[$key])) {
                    $skipped++;
                    continue;
                }

                $lines[] = implode("\t", [$pin, $punchedAt, $state, 1]);
            }

            if ($lines === []) {
                return $this->result(0, $skipped, 0, count($records), "Tidak ada log baru ({$skipped} sudah ada di sistem).");
            }

            $stats = $this->fingerprintAttendanceService->processAttlogPayload(
                $device,
                implode("\n", $lines),
            );

            $totalSkipped = $skipped + $stats['skipped'];

            $device->update(['last_seen_at' => now()]);

            return $this->result(
                $stats['processed'],
                $totalSkipped,
                $stats['failed'],
                count($records),
                "Berhasil tarik {$stats['processed']} log baru, {$totalSkipped} dilewati (sudah ada).",
            );
        } catch (Throwable $e) {
            Log::error('ZKTeco TCP pull failed', [
                'device' => $device->serial_number,
                'ip' => $device->ip_address,
                'error' => $e->getMessage(),
            ]);

            return $this->result(0, 0, 0, 0, 'Gagal tarik log: '.$e->getMessage());
        }
    }

    /**
     * @return array{processed: int, skipped: int, failed: int, devices: int}
     */
    public function pullAllActiveDevices(): array
    {
        $devices = FingerprintDevice::query()
            ->where('is_active', true)
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->where('serial_number', '!=', 'unknown')
            ->get();

        $totals = [
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'devices' => $devices->count(),
        ];

        foreach ($devices as $device) {
            $result = $this->pullFromDevice($device);
            $totals['processed'] += $result['processed'];
            $totals['skipped'] += $result['skipped'];
            $totals['failed'] += $result['failed'];
        }

        return $totals;
    }

    /**
     * @return array<string, true>
     */
    private function existingLogKeys(FingerprintDevice $device): array
    {
        return FingerprintLog::query()
            ->where('fingerprint_device_id', $device->id)
            ->get(['device_pin', 'punched_at', 'punch_status'])
            ->mapWithKeys(function (FingerprintLog $log) {
                $key = $log->device_pin.'|'.$log->punched_at->format('Y-m-d H:i:s').'|'.$log->punch_status;

                return [$key => true];
            })
            ->all();
    }

    /**
     * @return array{processed: int, skipped: int, failed: int, total: int, message: string}
     */
    private function result(int $processed, int $skipped, int $failed, int $total, string $message): array
    {
        return compact('processed', 'skipped', 'failed', 'total', 'message');
    }
}
