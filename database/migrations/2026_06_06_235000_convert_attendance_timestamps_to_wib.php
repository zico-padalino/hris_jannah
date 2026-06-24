<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data absensi sebelumnya disimpan dengan timezone UTC (default Laravel).
     * Konversi ke WIB agar waktu tampil sesuai jam lokal Indonesia.
     */
    public function up(): void
    {
        if (config('app.timezone') !== 'Asia/Jakarta') {
            return;
        }

        DB::table('attendances')->update([
            'attended_at' => DB::raw('DATE_ADD(attended_at, INTERVAL 7 HOUR)'),
        ]);

        DB::table('employee_faces')->update([
            'enrolled_at' => DB::raw('DATE_ADD(enrolled_at, INTERVAL 7 HOUR)'),
        ]);
    }

    public function down(): void
    {
        DB::table('attendances')->update([
            'attended_at' => DB::raw('DATE_SUB(attended_at, INTERVAL 7 HOUR)'),
        ]);

        DB::table('employee_faces')->update([
            'enrolled_at' => DB::raw('DATE_SUB(enrolled_at, INTERVAL 7 HOUR)'),
        ]);
    }
};
