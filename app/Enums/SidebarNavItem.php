<?php

namespace App\Enums;

enum SidebarNavItem: string
{
    case Dashboard = 'dashboard';
    case SectionAttendance = 'section_attendance';
    case AttendanceScan = 'attendance_scan';
    case AttendanceHistory = 'attendance_history';
    case AttendanceManage = 'attendance_manage';
    case FingerprintDevices = 'fingerprint_devices';
    case SectionLeave = 'section_leave';
    case LeaveHistory = 'leave_history';
    case LeaveCreate = 'leave_create';
    case LeaveApproval = 'leave_approval';
    case SectionPayroll = 'section_payroll';
    case Payroll = 'payroll';
    case SectionMaster = 'section_master';
    case Branches = 'branches';
    case Departments = 'departments';
    case Positions = 'positions';
    case Employees = 'employees';
    case ShiftTemplates = 'shift_templates';
    case EmployeeShifts = 'employee_shifts';
    case Holidays = 'holidays';
    case SectionSystem = 'section_system';
    case Reports = 'reports';
    case Users = 'users';
    case Roles = 'roles';
    case Announcements = 'announcements';
    case Settings = 'settings';
    case ActivityLogs = 'activity_logs';

    public function isSection(): bool
    {
        return str_starts_with($this->value, 'section_');
    }

    public function navLabelKey(): string
    {
        $key = match ($this) {
            self::AttendanceScan => 'scan_attendance',
            self::AttendanceManage => 'manage_attendance',
            default => $this->value,
        };

        return 'nav.'.$key;
    }

    /** @return list<Permission> */
    public function permissions(): array
    {
        return match ($this) {
            self::Dashboard => [Permission::DashboardView],
            self::SectionAttendance, self::SectionLeave, self::SectionPayroll,
            self::SectionMaster, self::SectionSystem => [],
            self::AttendanceScan => [Permission::AttendanceScan],
            self::AttendanceHistory => [Permission::AttendanceViewAll, Permission::AttendanceViewOwn],
            self::AttendanceManage => [Permission::AttendanceManage],
            self::FingerprintDevices => [Permission::FingerprintManage],
            self::LeaveHistory => [Permission::LeaveRequest, Permission::LeaveViewOwn],
            self::LeaveCreate => [Permission::LeaveRequest],
            self::LeaveApproval => [Permission::LeaveApprove],
            self::Payroll => [Permission::PayrollManage, Permission::PayrollViewOwn],
            self::Branches => [Permission::BranchesManage],
            self::Departments => [Permission::DepartmentsManage],
            self::Positions => [Permission::PositionsManage],
            self::Employees => [Permission::EmployeesManage],
            self::ShiftTemplates, self::EmployeeShifts => [Permission::ShiftsManage],
            self::Holidays => [Permission::HolidaysManage],
            self::Reports => [Permission::ReportsView],
            self::Users => [Permission::UsersManage],
            self::Roles => [Permission::RolesView],
            self::Announcements => [Permission::AnnouncementsManage],
            self::Settings => [Permission::SettingsManage],
            self::ActivityLogs => [Permission::ActivityLogView],
        };
    }

  /** @return list<self> */
    public function children(): array
    {
        return match ($this) {
            self::SectionAttendance => [
                self::AttendanceScan,
                self::AttendanceHistory,
                self::AttendanceManage,
                self::FingerprintDevices,
            ],
            self::SectionLeave => [
                self::LeaveHistory,
                self::LeaveCreate,
                self::LeaveApproval,
            ],
            self::SectionPayroll => [self::Payroll],
            self::SectionMaster => [
                self::Branches,
                self::Departments,
                self::Positions,
                self::Employees,
                self::ShiftTemplates,
                self::EmployeeShifts,
                self::Holidays,
            ],
            self::SectionSystem => [
                self::Reports,
                self::ActivityLogs,
                self::Announcements,
                self::Users,
                self::Roles,
                self::Settings,
            ],
            default => [],
        };
    }

  /** @return list<self> */
    public static function linkItems(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $item) => ! $item->isSection() && $item !== self::Dashboard
        ));
    }

  /** @return list<self> */
    public static function sections(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $item) => $item->isSection()
        ));
    }

    public function parentSection(): ?self
    {
        return match ($this) {
            self::Dashboard => null,
            self::AttendanceScan, self::AttendanceHistory, self::AttendanceManage, self::FingerprintDevices => self::SectionAttendance,
            self::LeaveHistory, self::LeaveCreate, self::LeaveApproval => self::SectionLeave,
            self::Payroll => self::SectionPayroll,
            self::Branches, self::Departments, self::Positions, self::Employees, self::ShiftTemplates, self::EmployeeShifts, self::Holidays => self::SectionMaster,
            self::Reports, self::Users, self::Roles, self::Announcements, self::Settings, self::ActivityLogs => self::SectionSystem,
            default => null,
        };
    }

    public function showsAttendanceFingerprintNotice(): bool
    {
        return in_array($this, [
            self::AttendanceScan,
            self::AttendanceHistory,
            self::AttendanceManage,
        ], true);
    }

  /** @return list<self> */
    public static function defaultNavOrder(): array
    {
        return [
            self::Dashboard,
            ...self::linkItems(),
        ];
    }

    public static function fromRouteName(?string $routeName): ?self
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        return match (true) {
            $routeName === 'dashboard' => self::Dashboard,
            str_starts_with($routeName, 'attendance.scan') => self::AttendanceScan,
            in_array($routeName, ['attendances.index', 'employees.attendances'], true) => self::AttendanceHistory,
            str_starts_with($routeName, 'attendances.') => self::AttendanceManage,
            str_starts_with($routeName, 'fingerprint-devices') => self::FingerprintDevices,
            in_array($routeName, ['leaves.index', 'leaves.proof'], true) => self::LeaveHistory,
            str_starts_with($routeName, 'leaves.') => self::LeaveCreate,
            str_starts_with($routeName, 'leave-approvals.') => self::LeaveApproval,
            str_starts_with($routeName, 'payrolls.') => self::Payroll,
            str_starts_with($routeName, 'branches.'),
            str_starts_with($routeName, 'branch-locations.') => self::Branches,
            str_starts_with($routeName, 'departments.') => self::Departments,
            str_starts_with($routeName, 'positions.') => self::Positions,
            str_starts_with($routeName, 'employees.'),
            str_starts_with($routeName, 'faces.') => self::Employees,
            str_starts_with($routeName, 'shifts.') => self::ShiftTemplates,
            str_starts_with($routeName, 'employee-shifts.') => self::EmployeeShifts,
            str_starts_with($routeName, 'holidays.') => self::Holidays,
            str_starts_with($routeName, 'reports.') => self::Reports,
            str_starts_with($routeName, 'announcements.') => self::Announcements,
            str_starts_with($routeName, 'users.') => self::Users,
            str_starts_with($routeName, 'roles.') => self::Roles,
            str_starts_with($routeName, 'activity-logs.') => self::ActivityLogs,
            str_starts_with($routeName, 'settings.') => self::Settings,
            default => null,
        };
    }

    public function settingsRoutePath(): ?string
    {
        if ($this->isSection()) {
            return null;
        }

        return match ($this) {
            self::Dashboard => route('dashboard', [], false),
            self::AttendanceScan => route('attendance.scan', [], false),
            self::AttendanceHistory => route('attendances.index', [], false),
            self::AttendanceManage => route('attendances.manage', [], false),
            self::FingerprintDevices => route('fingerprint-devices.index', [], false),
            self::LeaveHistory => route('leaves.index', [], false),
            self::LeaveCreate => route('leaves.create', [], false),
            self::LeaveApproval => route('leave-approvals.index', ['status' => 'pending'], false),
            self::Payroll => route('payrolls.index', [], false),
            self::Branches => route('branches.index', [], false),
            self::Departments => route('departments.index', [], false),
            self::Positions => route('positions.index', [], false),
            self::Employees => route('employees.index', [], false),
            self::ShiftTemplates => route('shifts.index', [], false),
            self::EmployeeShifts => route('employee-shifts.index', [], false),
            self::Holidays => route('holidays.index', [], false),
            self::Reports => route('reports.index', [], false),
            self::Announcements => route('announcements.index', [], false),
            self::Users => route('users.index', [], false),
            self::Roles => route('roles.index', [], false),
            self::ActivityLogs => route('activity-logs.index', [], false),
            self::Settings => route('settings.index', [], false),
            default => null,
        };
    }
}
