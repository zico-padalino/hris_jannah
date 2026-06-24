<?php

namespace App\Enums;

enum LeaveType: string
{
    case Annual = 'annual';
    case Sick = 'sick';
    case Permission = 'permission';
    case Overtime = 'overtime';

    public function label(): string
    {
        return __('enums.leave_type.'.$this->value);
    }

    /** @return 'cuti'|'izin'|'lembur' */
    public function approvalCategory(): string
    {
        return match ($this) {
            self::Annual, self::Sick => 'cuti',
            self::Permission => 'izin',
            self::Overtime => 'lembur',
        };
    }

    /** @return list<self> */
    public static function forApprovalCategory(string $category): array
    {
        return match ($category) {
            'cuti' => [self::Annual, self::Sick],
            'izin' => [self::Permission],
            'lembur' => [self::Overtime],
            default => [],
        };
    }
}
