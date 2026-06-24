<?php

namespace App\Models;

use App\Enums\AttendanceSource;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_request_id',
        'branch_id',
        'branch_location_id',
        'fingerprint_device_id',
        'type',
        'source',
        'attended_at',
        'latitude',
        'longitude',
        'photo_path',
        'face_match_score',
        'face_verified',
        'location_verified',
        'distance_meters',
        'status',
        'is_late',
        'late_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => AttendanceType::class,
            'source' => AttendanceSource::class,
            'attended_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'face_match_score' => 'decimal:4',
            'face_verified' => 'boolean',
            'location_verified' => 'boolean',
            'is_late' => 'boolean',
            'late_minutes' => 'integer',
            'distance_meters' => 'integer',
            'status' => AttendanceStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchLocation(): BelongsTo
    {
        return $this->belongsTo(BranchLocation::class);
    }

    public function fingerprintDevice(): BelongsTo
    {
        return $this->belongsTo(FingerprintDevice::class);
    }

    public function hasPhoto(): bool
    {
        return filled($this->photo_path) && Storage::disk('public')->exists($this->photo_path);
    }

    public function payrollDeductionAmount(): float
    {
        return $this->status->hasPayrollDeduction()
            ? SystemSetting::payrollDeductionPerAttendance()
            : 0;
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->hasPhoto()) {
                return null;
            }

            return '/storage/'.ltrim($this->photo_path, '/');
        });
    }
}
