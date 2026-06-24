<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$pullInterval = max(10, (int) config('attendance.fingerprint_auto_pull_seconds', 30));

$pullSchedule = Schedule::command('fingerprint:pull-logs')
    ->withoutOverlapping()
    ->when(fn () => in_array(config('attendance.fingerprint_log_mode'), ['tcp', 'scheduled'], true));

match (true) {
    $pullInterval <= 10 => $pullSchedule->everyTenSeconds(),
    $pullInterval <= 15 => $pullSchedule->everyFifteenSeconds(),
    $pullInterval <= 30 => $pullSchedule->everyThirtySeconds(),
    default => $pullSchedule->everyMinute(),
};
