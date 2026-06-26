<?php

namespace App\Models;

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'proof_path',
        'status',
        'approved_by',
        'approved_at',
        'employee_status_read_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => LeaveType::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => LeaveStatus::class,
            'approved_at' => 'datetime',
            'employee_status_read_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function hasProof(): bool
    {
        return filled($this->proof_path) && Storage::disk('public')->exists($this->proof_path);
    }

    public function proofExtension(): ?string
    {
        if (! $this->hasProof()) {
            return null;
        }

        return strtolower(pathinfo($this->proof_path, PATHINFO_EXTENSION));
    }

    public function proofIsImage(): bool
    {
        return in_array($this->proofExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    public function proofIsPdf(): bool
    {
        return $this->proofExtension() === 'pdf';
    }

    protected function proofUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->hasProof()) {
                return null;
            }

            return route('leaves.proof', $this);
        });
    }
}
