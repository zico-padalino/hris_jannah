<?php

namespace App\Console\Commands;

use App\Services\ZktecoTcpPullService;
use Illuminate\Console\Command;

class PullFingerprintLogsCommand extends Command
{
    protected $signature = 'fingerprint:pull-logs';

    protected $description = 'Tarik log absensi dari semua mesin fingerprint aktif via TCP';

    public function handle(ZktecoTcpPullService $tcpPullService): int
    {
        if (! extension_loaded('sockets')) {
            $this->error('Ekstensi PHP "sockets" belum aktif.');

            return self::FAILURE;
        }

        $summary = $tcpPullService->pullAllActiveDevices();

        if ($summary['devices'] === 0) {
            $this->warn('Tidak ada mesin fingerprint aktif dengan IP. Isi IP di halaman Kelola Mesin.');

            return self::SUCCESS;
        }

        $this->info("Selesai. {$summary['processed']} log baru diproses dari {$summary['devices']} mesin.");

        return self::SUCCESS;
    }
}
