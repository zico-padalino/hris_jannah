<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('fingerprint_devices', 'attlog_stamp')) {
            DB::statement('ALTER TABLE fingerprint_devices ADD COLUMN attlog_stamp BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER last_seen_at');
        }

        if (! Schema::hasColumn('fingerprint_devices', 'operlog_stamp')) {
            DB::statement('ALTER TABLE fingerprint_devices ADD COLUMN operlog_stamp BIGINT UNSIGNED NOT NULL DEFAULT 0 AFTER attlog_stamp');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fingerprint_devices', 'operlog_stamp')) {
            DB::statement('ALTER TABLE fingerprint_devices DROP COLUMN operlog_stamp');
        }

        if (Schema::hasColumn('fingerprint_devices', 'attlog_stamp')) {
            DB::statement('ALTER TABLE fingerprint_devices DROP COLUMN attlog_stamp');
        }
    }
};
