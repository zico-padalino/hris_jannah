<?php

namespace App\Enums;

enum Permission: string
{
    case DashboardView = 'dashboard.view';
    case AttendanceScan = 'attendance.scan';
    case AttendanceViewOwn = 'attendance.view_own';
    case AttendanceViewAll = 'attendance.view_all';
    case AttendanceManage = 'attendance.manage';
    case UsersManage = 'users.manage';
    case BranchesManage = 'branches.manage';
    case DepartmentsManage = 'departments.manage';
    case PositionsManage = 'positions.manage';
    case EmployeesManage = 'employees.manage';
    case FacesEnroll = 'faces.enroll';
    case ShiftsManage = 'shifts.manage';
    case HolidaysManage = 'holidays.manage';
    case LeaveRequest = 'leave.request';
    case LeaveViewOwn = 'leave.view_own';
    case LeaveApprove = 'leave.approve';
    case PayrollManage = 'payroll.manage';
    case PayrollViewOwn = 'payroll.view_own';
    case ReportsView = 'reports.view';
    case SettingsManage = 'settings.manage';
    case RolesView = 'roles.view';
    case FingerprintManage = 'fingerprint.manage';
    case AnnouncementsManage = 'announcements.manage';

    public function label(): string
    {
        return __('enums.permission.'.$this->translationKey());
    }

    public function group(): string
    {
        return match ($this) {
            self::DashboardView => __('enums.permission_group.general'),
            self::AttendanceScan, self::AttendanceViewOwn, self::AttendanceViewAll, self::AttendanceManage, self::FingerprintManage => __('enums.permission_group.attendance'),
            self::UsersManage, self::RolesView => __('enums.permission_group.users'),
            self::BranchesManage, self::DepartmentsManage, self::PositionsManage, self::EmployeesManage, self::FacesEnroll => __('enums.permission_group.master_data'),
            self::ShiftsManage, self::HolidaysManage => __('enums.permission_group.schedule'),
            self::LeaveRequest, self::LeaveViewOwn, self::LeaveApprove => __('enums.permission_group.leave'),
            self::PayrollManage, self::PayrollViewOwn => __('enums.permission_group.payroll'),
            self::ReportsView => __('enums.permission_group.reports'),
            self::SettingsManage, self::AnnouncementsManage => __('enums.permission_group.system'),
        };
    }

    private function translationKey(): string
    {
        return str_replace('.', '_', $this->value);
    }
}
