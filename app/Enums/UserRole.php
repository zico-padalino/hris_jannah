<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Hr = 'hr';
    case BranchAdmin = 'branch_admin';
    case Employee = 'employee';

    public function label(): string
    {
        return __('enums.user_role.'.$this->value);
    }

    public function defaultDescription(): string
    {
        return __('enums.user_role_description.'.$this->value);
    }

    public function isProtected(): bool
    {
        return $this === self::SuperAdmin;
    }
}
