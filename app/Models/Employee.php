<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'department_id',
        'position_id',
        'shift_id',
        'is_non_shift',
        'employee_number',
        'fingerprint_pin',
        'name',
        'email',
        'phone',
        'address',
        'employment_status',
        'base_salary',
        'join_date',
        'contract_start_date',
        'contract_end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'join_date' => 'date',
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'is_active' => 'boolean',
            'is_non_shift' => 'boolean',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function faces(): HasMany
    {
        return $this->hasMany(EmployeeFace::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function shiftSelection(): string
    {
        if ($this->is_non_shift) {
            return 'non_shift';
        }

        if ($this->shift_id) {
            return (string) $this->shift_id;
        }

        return 'unset';
    }

    public function shiftLabel(): string
    {
        if ($this->is_non_shift) {
            return 'Non Shift';
        }

        if ($this->relationLoaded('shift') && $this->shift) {
            return $this->shift->name;
        }

        return $this->shift_id ? ($this->shift?->name ?? '—') : 'Belum diatur';
    }

    public function canRecordAttendance(): bool
    {
        return $this->is_non_shift || $this->shift_id !== null;
    }

    /** @return array{is_non_shift: bool, shift_id: int|null} */
    public static function shiftFieldsFromSelection(string $selection): array
    {
        if ($selection === 'non_shift') {
            return ['is_non_shift' => true, 'shift_id' => null];
        }

        if ($selection === 'unset' || $selection === '') {
            return ['is_non_shift' => false, 'shift_id' => null];
        }

        return ['is_non_shift' => false, 'shift_id' => (int) $selection];
    }

    public function shiftAssignmentChanged(array $shiftFields): bool
    {
        return (bool) $this->is_non_shift !== (bool) $shiftFields['is_non_shift']
            || (int) ($this->shift_id ?? 0) !== (int) ($shiftFields['shift_id'] ?? 0);
    }
}
