<?php

namespace App\Console\Commands;

use App\Services\ZktecoTcpPullService;
use Illuminate\Console\Command;

class WatchFingerprintLogsCommand extends Command
{
    protected $signature = 'fingerprint:watch';

    protected $description = 'Tarik log absensi via TCP secara berkala (mode TCP)';

    public function handle(ZktecoTcpPullService $tcpPullService): int
    {
        if (! extension_loaded('sockets')) {
            $this->error('Ekstensi PHP "sockets" belum aktif.');

            return self::FAILURE;
        }

        $mode = config('attendance.fingerprint_log_mode', 'tcp');
        if (! in_array($mode, ['tcp', 'scheduled'], true)) {
            $this->warn("Mode saat ini: {$mode}. Set FINGERPRINT_LOG_MODE=tcp di .env.");
        }

        $interval = max(10, (int) config('attendance.fingerprint_auto_pull_seconds', 30));
        $this->info("Mode TCP — tarik log setiap {$interval} detik. Tekan Ctrl+C untuk berhenti.");

        while (true) {
            $summary = $tcpPullService->pullAllActiveDevices();

            if ($summary['devices'] === 0) {
                $this->line('[--] Tidak ada mesin aktif dengan IP. Isi IP mesin di halaman Kelola Mesin.');
            } else {
                $this->line(sprintf(
                    '[%s] %d mesin · %d log baru · %d dilewati',
                    now()->format('H:i:s'),
                    $summary['devices'],
                    $summary['processed'],
                    $summary['skipped'],
                ));
            }

            sleep($interval);
        }
    }
}
