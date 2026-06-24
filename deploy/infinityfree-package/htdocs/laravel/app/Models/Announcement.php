<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'branch_id',
        'created_by',
        'title',
        'content',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isWithinPeriod(): bool
    {
        $today = today();

        return $today->greaterThanOrEqualTo($this->starts_at)
            && $today->lessThanOrEqualTo($this->ends_at);
    }

    public function isPublished(): bool
    {
        return $this->is_active && $this->isWithinPeriod();
    }

    public function status(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        $today = today();

        if ($today->lt($this->starts_at)) {
            return 'scheduled';
        }

        if ($today->gt($this->ends_at)) {
            return 'expired';
        }

        return 'active';
    }

    public function statusLabel(): string
    {
        return __('pages.announcements.status_'.$this->status());
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status()) {
            'active' => 'bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-200',
            'scheduled' => 'bg-sky-100 text-sky-900 dark:bg-sky-950 dark:text-sky-200',
            'expired' => 'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-200',
            default => 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
        };
    }
}
