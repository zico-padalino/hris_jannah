<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Valid = 'valid';
    case Late = 'late';
    case InvalidFace = 'invalid_face';
    case InvalidLocation = 'invalid_location';
    case InvalidBoth = 'invalid_both';

    public function label(): string
    {
        return __('enums.attendance_status.'.$this->value);
    }

    public function isSuccessful(): bool
    {
        return in_array($this, [self::Valid, self::Late], true);
    }

    public function hasPayrollDeduction(): bool
    {
        return in_array($this, [
            self::Late,
            self::InvalidFace,
            self::InvalidLocation,
            self::InvalidBoth,
        ], true);
    }
}
