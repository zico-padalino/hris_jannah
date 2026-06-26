<?php

namespace App\Enums;

enum ActivityLogAction: string
{
    case Login = 'login';
    case Logout = 'logout';
    case LoginFailed = 'login_failed';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';

    public function label(): string
    {
        return __('enums.activity_log_action.'.$this->value);
    }

    public function badgeClass(): string
    {
        return 'activity-log-badge activity-log-badge--'.$this->value;
    }
}
