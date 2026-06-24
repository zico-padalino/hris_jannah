<?php

namespace App\Models;

use App\Enums\ActivityLogAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'branch_id',
        'user_name',
        'user_email',
        'user_role',
        'action',
        'module',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'action' => ActivityLogAction::class,
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function subjectDisplay(): string
    {
        if ($this->subject_label) {
            if ($this->subject_id) {
                return $this->subject_label.' (#'.$this->subject_id.')';
            }

            return $this->subject_label;
        }

        if ($this->subject_id) {
            return '#'.$this->subject_id;
        }

        return '—';
    }
}
