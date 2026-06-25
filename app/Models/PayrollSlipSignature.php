<?php

namespace App\Models;

use App\Enums\PayrollSlipSignatureStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSlipSignature extends Model
{
    protected $fillable = [
        'payroll_item_id',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PayrollSlipSignatureStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItem::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
