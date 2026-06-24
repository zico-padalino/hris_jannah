<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FingerprintDevice extends Model
{
    protected $fillable = [
        'serial_number',
        'name',
        'model',
        'branch_id',
        'ip_address',
        'last_seen_at',
        'attlog_stamp',
        'operlog_stamp',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FingerprintLog::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(FingerprintDeviceCommand::class);
    }

    public function isOnline(int $thresholdSeconds = 180): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->greaterThan(now()->subSeconds($thresholdSeconds));
    }
}
