<?php

namespace App\Console\Commands;

use App\Services\ShiftScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculateAttendanceLatenessCommand extends Command
{
    protected $signature = 'attendance:recalculate-lateness
                            {--from= : Tanggal awal (Y-m-d)}
                            {--until= : Tanggal akhir (Y-m-d)}';

    protected $description = 'Hitung ulang status keterlambatan absensi berdasarkan jadwal shift pegawai saat ini';

    public function handle(ShiftScheduleService $shiftScheduleService): int
    {
        $from = $this->option('from') ? Carbon::parse($this->option('from')) : null;
        $until = $this->option('until') ? Carbon::parse($this->option('until')) : null;

        $result = $shiftScheduleService->recalculateStoredAttendances($from, $until);

        $this->info("Selesai. Dicek: {$result['checked']}, diperbarui: {$result['updated']}.");

        return self::SUCCESS;
    }
}
