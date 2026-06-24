<?php

namespace App\Enums;

enum AttendanceType: string
{
    case CheckIn = 'check_in';
    case CheckOut = 'check_out';
    case Leave = 'leave';

    public function label(): string
    {
        return __('enums.attendance_type.'.$this->value);
    }
}
