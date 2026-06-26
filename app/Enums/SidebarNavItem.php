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
    case SectionPengajuan = 'section_pengajuan';
    case SectionLeaveCuti = 'section_leave_cuti';
    case LeaveCutiCreate = 'leave_cuti_create';
    case LeaveCutiHistory = 'leave_cuti_history';
    case LeaveCutiApproval = 'leave_cuti_approval';
    case SectionLeaveIzin = 'section_leave_izin';
    case LeaveIzinCreate = 'leave_izin_create';
    case LeaveIzinHistory = 'leave_izin_history';
    case LeaveIzinApproval = 'leave_izin_approval';
    case SectionLeaveLembur = 'section_leave_lembur';
    case LeaveLemburCreate = 'leave_lembur_create';
    case LeaveLemburHistory = 'leave_lembur_history';
    case LeaveLemburApproval = 'leave_lembur_approval';
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

    public function isLeaveSection(): bool
    {
        return $this === self::SectionPengajuan || $this->isLeaveSubSection();
    }

    public function isLeaveSubSection(): bool
    {
        return in_array($this, [
            self::SectionLeaveCuti,
            self::SectionLeaveIzin,
            self::SectionLeaveLembur,
        ], true);
    }

    public function leaveSubSection(): ?self
    {
        return match ($this) {
            self::LeaveCutiCreate, self::LeaveCutiHistory, self::LeaveCutiApproval => self::SectionLeaveCuti,
            self::LeaveIzinCreate, self::LeaveIzinHistory, self::LeaveIzinApproval => self::SectionLeaveIzin,
            self::LeaveLemburCreate, self::LeaveLemburHistory, self::LeaveLemburApproval => self::SectionLeaveLembur,
            default => null,
        };
    }

    /** @return 'cuti'|'izin'|'lembur'|null */
    public function leaveApprovalCategory(): ?string
    {
        return match ($this) {
            self::LeaveCutiCreate, self::LeaveCutiHistory, self::LeaveCutiApproval => 'cuti',
            self::LeaveIzinCreate, self::LeaveIzinHistory, self::LeaveIzinApproval => 'izin',
            self::LeaveLemburCreate, self::LeaveLemburHistory, self::LeaveLemburApproval => 'lembur',
            default => null,
        };
    }

    public function isLeaveHistory(): bool
    {
        return in_array($this, [
            self::LeaveCutiHistory,
            self::LeaveIzinHistory,
            self::LeaveLemburHistory,
        ], true);
    }

    public function isLeaveCreate(): bool
    {
        return in_array($this, [
            self::LeaveCutiCreate,
            self::LeaveIzinCreate,
            self::LeaveLemburCreate,
        ], true);
    }

    public function isLeaveApproval(): bool
    {
        return in_array($this, [
            self::LeaveCutiApproval,
            self::LeaveIzinApproval,
            self::LeaveLemburApproval,
        ], true);
    }

    public function navLabelKey(): string
    {
        $key = match ($this) {
            self::AttendanceScan => 'scan_attendance',
            self::AttendanceManage => 'manage_attendance',
            self::LeaveCutiCreate, self::LeaveIzinCreate, self::LeaveLemburCreate => 'leave_pengajuan',
            self::LeaveCutiHistory, self::LeaveIzinHistory, self::LeaveLemburHistory => 'leave_riwayat',
            default => $this->value,
        };

        return 'nav.'.$key;
    }

    /** @return list<Permission> */
    public function permissions(): array
    {
        return match ($this) {
            self::Dashboard => [Permission::DashboardView],
            self::SectionAttendance, self::SectionPengajuan, self::SectionLeaveCuti, self::SectionLeaveIzin, self::SectionLeaveLembur,
            self::SectionPayroll, self::SectionMaster, self::SectionSystem => [],
            self::AttendanceScan => [Permission::AttendanceScan],
            self::AttendanceHistory => [Permission::AttendanceViewAll, Permission::AttendanceViewOwn],
            self::AttendanceManage => [Permission::AttendanceManage],
            self::FingerprintDevices => [Permission::FingerprintManage],
            self::LeaveCutiHistory, self::LeaveIzinHistory, self::LeaveLemburHistory => [Permission::LeaveRequest, Permission::LeaveViewOwn],
            self::LeaveCutiCreate, self::LeaveIzinCreate, self::LeaveLemburCreate => [Permission::LeaveRequest],
            self::LeaveCutiApproval, self::LeaveIzinApproval, self::LeaveLemburApproval => [Permission::LeaveApprove],
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
            self::SectionPengajuan => [
                self::LeaveCutiCreate,
                self::LeaveCutiHistory,
                self::LeaveIzinCreate,
                self::LeaveIzinHistory,
                self::LeaveLemburCreate,
                self::LeaveLemburHistory,
            ],
            self::SectionLeaveCuti => [
                self::LeaveCutiCreate,
                self::LeaveCutiHistory,
            ],
            self::SectionLeaveIzin => [
                self::LeaveIzinCreate,
                self::LeaveIzinHistory,
            ],
            self::SectionLeaveLembur => [
                self::LeaveLemburCreate,
                self::LeaveLemburHistory,
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
            fn (self $item) => $item->isSection() && ! $item->isLeaveSubSection()
        ));
    }

    /** @return list<self> */
    public static function leaveApprovalItems(): array
    {
        return [
            self::LeaveCutiApproval,
            self::LeaveIzinApproval,
            self::LeaveLemburApproval,
        ];
    }

    /** @return list<self> */
    public static function leaveHistoryItems(): array
    {
        return [
            self::LeaveCutiHistory,
            self::LeaveIzinHistory,
            self::LeaveLemburHistory,
        ];
    }

    public function parentSection(): ?self
    {
        return match ($this) {
            self::Dashboard => null,
            self::AttendanceScan, self::AttendanceHistory, self::AttendanceManage, self::FingerprintDevices => self::SectionAttendance,
            self::LeaveCutiCreate, self::LeaveCutiHistory, self::LeaveCutiApproval,
            self::LeaveIzinCreate, self::LeaveIzinHistory, self::LeaveIzinApproval,
            self::LeaveLemburCreate, self::LeaveLemburHistory, self::LeaveLemburApproval => self::SectionPengajuan,
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

    public static function fromRouteName(?string $routeName, ?string $category = null): ?self
    {
        if ($routeName === null || $routeName === '') {
            return null;
        }

        return match (true) {
            $routeName === 'dashboard' => self::Dashboard,
            str_starts_with($routeName, 'attendance.scan') => self::AttendanceScan,
            in_array($routeName, ['attendances.index', 'employees.attendances'], true) => self::leaveHistoryFromCategory($category),
            str_starts_with($routeName, 'attendances.') => self::AttendanceManage,
            str_starts_with($routeName, 'fingerprint-devices') => self::FingerprintDevices,
            in_array($routeName, ['leaves.index', 'leaves.proof'], true) => self::leaveHistoryFromCategory($category),
            $routeName === 'leaves.create' => self::leaveCreateFromCategory($category),
            str_starts_with($routeName, 'leaves.') => self::leaveCreateFromCategory($category),
            str_starts_with($routeName, 'leave-approvals.') => self::leaveApprovalFromCategory($category),
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

        $category = $this->leaveApprovalCategory();

        return match ($this) {
            self::Dashboard => route('dashboard', [], false),
            self::AttendanceScan => route('attendance.scan', [], false),
            self::AttendanceHistory => route('attendances.index', [], false),
            self::AttendanceManage => route('attendances.manage', [], false),
            self::FingerprintDevices => route('fingerprint-devices.index', [], false),
            self::LeaveCutiHistory, self::LeaveIzinHistory, self::LeaveLemburHistory => route('leaves.index', ['category' => $category], false),
            self::LeaveCutiCreate, self::LeaveIzinCreate, self::LeaveLemburCreate => route('leaves.create', ['category' => $category], false),
            self::LeaveCutiApproval, self::LeaveIzinApproval, self::LeaveLemburApproval => route('leave-approvals.index', ['status' => 'pending', 'category' => $category], false),
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

    private static function leaveHistoryFromCategory(?string $category): self
    {
        return match ($category) {
            'izin' => self::LeaveIzinHistory,
            'lembur' => self::LeaveLemburHistory,
            default => self::LeaveCutiHistory,
        };
    }

    private static function leaveCreateFromCategory(?string $category): self
    {
        return match ($category) {
            'izin' => self::LeaveIzinCreate,
            'lembur' => self::LeaveLemburCreate,
            default => self::LeaveCutiCreate,
        };
    }

    private static function leaveApprovalFromCategory(?string $category): self
    {
        return match ($category) {
            'izin' => self::LeaveIzinApproval,
            'lembur' => self::LeaveLemburApproval,
            default => self::LeaveCutiApproval,
        };
    }
}
