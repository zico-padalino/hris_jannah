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
        return match ($this) {
            self::Login => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
            self::Logout => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
            self::LoginFailed => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200',
            self::Create => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
            self::Update => 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200',
            self::Delete => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200',
        };
    }
}
