<?php

namespace App\Enums;

enum AttendanceSource: string
{
    case Face = 'face';
    case Fingerprint = 'fingerprint';
    case Gps = 'gps';
    case Manual = 'manual';
    case Leave = 'leave';

    public function label(): string
    {
        return __('enums.attendance_source.'.$this->value);
    }
}
