<?php

namespace App\Services;

use App\Models\FingerprintDevice;
use App\Models\FingerprintDeviceCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZktecoAdmsService
{
    public function __construct(
        private readonly FingerprintAttendanceService $fingerprintAttendanceService,
        private readonly FingerprintLogPullCoordinator $logPullCoordinator,
    ) {}

    public function touchDevice(Request $request): ?FingerprintDevice
    {
        $serialNumber = trim((string) $request->query('SN', ''));

        if ($serialNumber === '') {
            Log::warning('ZKTeco request without serial number', [
                'ip' => $request->ip(),
                'method' => $request->method(),
                'path' => $request->path(),
                'table' => $request->query('table'),
            ]);

            return null;
        }

        $device = FingerprintDevice::query()->firstOrCreate(
            ['serial_number' => $serialNumber],
            [
                'name' => 'Mesin '.$serialNumber,
                'model' => 'X100-C',
                'is_active' => true,
            ],
        );

        $device->update([
            'ip_address' => $request->ip(),
            'last_seen_at' => now(),
        ]);

        $this->logPullCoordinator->maybePullOnDeviceActivity($device);

        return $device;
    }

    public function queuePullAttlogs(FingerprintDevice $device, int $days = 7): void
    {
        $this->resetAttlogStamp($device);

        $start = now()->subDays($days)->format('Y-m-d H:i:s');
        $end = now()->format('Y-m-d H:i:s');

        $device->commands()->create([
            'command' => 'CHECK',
            'status' => 'pending',
        ]);

        $device->commands()->create([
            'command' => "DATA QUERY ATTLOG StartTime={$start} EndTime={$end}",
            'status' => 'pending',
        ]);
    }

    public function resetAttlogStamp(FingerprintDevice $device): void
    {
        if ($this->hasStampColumns()) {
            $device->update([
                'attlog_stamp' => 0,
                'operlog_stamp' => 0,
            ]);
        }
    }

    public function handleCdataGet(Request $request): string
    {
        $this->logRequest($request);
        $device = $this->touchDevice($request);

        if ($device === null) {
            return 'OK';
        }

        $timezone = 7;
        $serialNumber = $device->serial_number;
        $attlogStamp = $this->attlogStampFor($device);
        $operlogStamp = $this->operlogStampFor($device);

        $lines = [
            'GET OPTION FROM: '.$serialNumber,
            'ATTLOGStamp='.$attlogStamp,
            'OPERLOGStamp='.$operlogStamp,
            'ErrorDelay=60',
            'Delay=10',
            'ResLogDay=18250',
            'ResLogDelCount=10000',
            'TransTimes=00:00;14:05',
            'TransInterval=1',
            'TransFlag=TransData AttLog	OpLog	AttPhoto	EnrollFP	UserPic',
            'Realtime=1',
            'Encrypt=0',
            'TimeZone='.$timezone,
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    public function handleCdataPost(Request $request): string
    {
        $this->logRequest($request);
        $device = $this->touchDevice($request);
        $table = strtoupper((string) $request->query('table', ''));
        $body = trim($request->getContent());

        if ($body === '' && $request->hasAny(array_keys($request->all()))) {
            $body = trim(implode("\n", array_filter($request->all(), 'is_string')));
        }

        if ($device === null) {
            return 'OK';
        }

        if ($body !== '') {
            Log::info('ZKTeco ADMS data received', [
                'sn' => $device->serial_number,
                'table' => $table,
                'bytes' => strlen($body),
                'preview' => substr($body, 0, 200),
            ]);
        }

        if ($table === 'ATTLOG' && $body !== '' && config('attendance.fingerprint_log_mode') !== 'tcp') {
            $stats = $this->fingerprintAttendanceService->processAttlogPayload($device, $body);

            Log::info('ZKTeco ATTLOG processed', [
                'sn' => $device->serial_number,
                'stats' => $stats,
            ]);

            $this->updateAttlogStamp($device, $request, $stats['processed'] + $stats['skipped']);
        }

        if ($table === 'OPERLOG' && $body !== '') {
            Log::info('ZKTeco OPERLOG', ['sn' => $device->serial_number, 'body' => $body]);

            $stamp = (int) $request->query('Stamp', 0);
            if ($stamp > 0 && $this->hasStampColumns()) {
                $device->update(['operlog_stamp' => $stamp]);
            }
        }

        return 'OK';
    }

    public function handleGetRequest(Request $request): string
    {
        $this->logRequest($request);
        $device = $this->touchDevice($request);

        if ($device === null) {
            return 'OK';
        }

        $command = FingerprintDeviceCommand::query()
            ->where('fingerprint_device_id', $device->id)
            ->where('status', 'pending')
            ->oldest()
            ->first();

        if ($command === null) {
            return 'OK';
        }

        $command->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return 'C:'.$command->id.':'.$command->command;
    }

    public function handleDeviceCmd(Request $request): string
    {
        $this->logRequest($request);
        $this->touchDevice($request);
        $body = trim($request->getContent());

        if (preg_match('/^C:(\d+):(.+)$/s', $body, $matches)) {
            FingerprintDeviceCommand::query()
                ->whereKey((int) $matches[1])
                ->update([
                    'status' => str_contains($body, 'OK') ? 'completed' : 'failed',
                    'response' => $body,
                    'completed_at' => now(),
                ]);
        }

        return 'OK';
    }

    public function handleRegistry(Request $request): string
    {
        $this->logRequest($request);
        $this->touchDevice($request);

        return 'OK';
    }

    private function logRequest(Request $request): void
    {
        Log::debug('ZKTeco ADMS request', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'path' => $request->path(),
            'sn' => $request->query('SN'),
            'table' => $request->query('table'),
            'stamp' => $request->query('Stamp'),
            'bytes' => strlen($request->getContent()),
        ]);
    }

    private function hasStampColumns(): bool
    {
        static $hasColumns = null;

        if ($hasColumns === null) {
            $hasColumns = \Illuminate\Support\Facades\Schema::hasColumn('fingerprint_devices', 'attlog_stamp');
        }

        return $hasColumns;
    }

    private function attlogStampFor(FingerprintDevice $device): int
    {
        if ($this->hasStampColumns()) {
            return (int) $device->attlog_stamp;
        }

        return 0;
    }

    private function operlogStampFor(FingerprintDevice $device): int
    {
        if ($this->hasStampColumns()) {
            return (int) $device->operlog_stamp;
        }

        return 0;
    }

    private function updateAttlogStamp(FingerprintDevice $device, Request $request, int $processedLines): void
    {
        if (! $this->hasStampColumns()) {
            return;
        }

        $stamp = (int) $request->query('Stamp', 0);

        if ($stamp > (int) $device->attlog_stamp) {
            $device->update(['attlog_stamp' => $stamp]);

            return;
        }

        if ($processedLines > 0) {
            $device->increment('attlog_stamp', $processedLines);
        }
    }
}
