<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Shift extends Model
{
    public const DEFAULT_WORK_DAYS = [1, 2, 3, 4, 5];

    /** @var array<int, string> */
    public const DAY_LABELS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /** @var array<int, string> */
    public const DAY_SHORT_LABELS = [
        1 => 'Sen',
        2 => 'Sel',
        3 => 'Rab',
        4 => 'Kam',
        5 => 'Jum',
        6 => 'Sab',
        7 => 'Min',
    ];

    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'start_time',
        'end_time',
        'work_days',
        'late_tolerance_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'work_days' => 'array',
            'late_tolerance_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /** @return list<int> */
    public function resolvedWorkDays(): array
    {
        $days = $this->work_days ?? self::DEFAULT_WORK_DAYS;

        return array_values(array_unique(array_map('intval', $days)));
    }

    public function workDaysLabel(bool $short = false): string
    {
        $labels = $short ? self::DAY_SHORT_LABELS : self::DAY_LABELS;

        return collect($this->resolvedWorkDays())
            ->sort()
            ->map(fn (int $day) => $labels[$day] ?? (string) $day)
            ->implode(', ');
    }

    public function formattedStartTime(): string
    {
        return substr((string) $this->start_time, 0, 5);
    }

    public function formattedEndTime(): string
    {
        return substr((string) $this->end_time, 0, 5);
    }

    public function workDurationLabel(): string
    {
        $start = Carbon::createFromFormat('H:i', $this->formattedStartTime());
        $end = Carbon::createFromFormat('H:i', $this->formattedEndTime());

        if ($end->lte($start)) {
            $end->addDay();
        }

        $totalMinutes = $start->diffInMinutes($end);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        if ($minutes === 0) {
            return $hours.' jam';
        }

        return $hours.' jam '.$minutes.' mnt';
    }

    public function workDaysCount(): int
    {
        return count($this->resolvedWorkDays());
    }
}
