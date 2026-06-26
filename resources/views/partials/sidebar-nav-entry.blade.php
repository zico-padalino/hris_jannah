@php
    use App\Enums\SidebarNavItem;

    /** @var SidebarNavItem|null $item */
    $item = $entry['item'] ?? null;
    $nested = $nested ?? false;
    $nestedClass = $nested ? ' nav-link--nested' : '';
    $pendingLeaveApprovalBreakdown = $pendingLeaveApprovalBreakdown ?? ['cuti' => 0, 'izin' => 0, 'lembur' => 0];
@endphp

@if($entry['type'] === 'custom_link')
    @php
        $module = $entry['module'] ?? ['label' => '', 'url' => '#'];
        $moduleUrl = $module['url'];
        $isExternal = str_starts_with($moduleUrl, 'http://') || str_starts_with($moduleUrl, 'https://');
        $modulePath = $isExternal ? null : '/'.ltrim(parse_url($moduleUrl, PHP_URL_PATH) ?? $moduleUrl, '/');
        $isActive = ! $isExternal && $modulePath !== null && request()->is(ltrim($modulePath, '/'));
    @endphp
    <a
        href="{{ $moduleUrl }}"
        class="{{ $linkClass }}{{ $nestedClass }} {{ $isActive ? $activeClass : $inactiveClass }}"
        @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
    >
        {{ $module['label'] }}
    </a>
@else
    @switch($item)
        @case(SidebarNavItem::Dashboard)
            <a href="{{ route('dashboard') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::AttendanceScan)
            <a href="{{ route('attendance.scan') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('attendance.scan*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::AttendanceHistory)
            <a href="{{ route('attendances.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('attendances.index') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::AttendanceManage)
            <a href="{{ route('attendances.manage') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('attendances.manage') || request()->routeIs('attendances.create') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::FingerprintDevices)
            <a href="{{ route('fingerprint-devices.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('fingerprint-devices.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::LeaveCutiHistory)
            <a href="{{ route('leaves.index', ['category' => 'cuti']) }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('leaves.index') && request('category', 'cuti') === 'cuti' ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::LeaveCutiCreate)
            <a href="{{ route('leaves.create', ['category' => 'cuti']) }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('leaves.create') && request('category', 'cuti') === 'cuti' ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::LeaveCutiApproval)
            @include('partials.leave-nav-link', [
                'href' => route('leave-approvals.index', ['status' => 'pending', 'category' => 'cuti']),
                'count' => ($pendingLeaveApprovalBreakdown['cuti'] ?? 0),
                'active' => request()->routeIs('leave-approvals.*') && request('category') === 'cuti',
                'label' => __($item->navLabelKey()),
                'pendingLabel' => __('app.new'),
                'mobile' => $mobile,
                'nested' => $nested,
            ])
            @break

        @case(SidebarNavItem::LeaveIzinHistory)
            <a href="{{ route('leaves.index', ['category' => 'izin']) }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('leaves.index') && request('category') === 'izin' ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::LeaveIzinCreate)
            <a href="{{ route('leaves.create', ['category' => 'izin']) }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('leaves.create') && request('category') === 'izin' ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::LeaveIzinApproval)
            @include('partials.leave-nav-link', [
                'href' => route('leave-approvals.index', ['status' => 'pending', 'category' => 'izin']),
                'count' => ($pendingLeaveApprovalBreakdown['izin'] ?? 0),
                'active' => request()->routeIs('leave-approvals.*') && request('category') === 'izin',
                'label' => __($item->navLabelKey()),
                'pendingLabel' => __('app.new'),
                'mobile' => $mobile,
                'nested' => $nested,
            ])
            @break

        @case(SidebarNavItem::LeaveLemburHistory)
            <a href="{{ route('leaves.index', ['category' => 'lembur']) }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('leaves.index') && request('category') === 'lembur' ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::LeaveLemburCreate)
            <a href="{{ route('leaves.create', ['category' => 'lembur']) }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('leaves.create') && request('category') === 'lembur' ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::LeaveLemburApproval)
            @include('partials.leave-nav-link', [
                'href' => route('leave-approvals.index', ['status' => 'pending', 'category' => 'lembur']),
                'count' => ($pendingLeaveApprovalBreakdown['lembur'] ?? 0),
                'active' => request()->routeIs('leave-approvals.*') && request('category') === 'lembur',
                'label' => __($item->navLabelKey()),
                'pendingLabel' => __('app.new'),
                'mobile' => $mobile,
                'nested' => $nested,
            ])
            @break

        @case(SidebarNavItem::Payroll)
            @include('partials.leave-nav-link', [
                'href' => route('payrolls.index'),
                'count' => ($canApprovePayroll ?? false) ? ($pendingPayrollTotal ?? 0) : 0,
                'active' => request()->routeIs('payrolls.*'),
                'label' => __($item->navLabelKey()),
                'pendingLabel' => __('app.new'),
                'mobile' => $mobile,
                'nested' => $nested,
            ])
            @break

        @case(SidebarNavItem::Branches)
            <a href="{{ route('branches.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('branches.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Departments)
            <a href="{{ route('departments.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('departments.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Positions)
            <a href="{{ route('positions.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('positions.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Employees)
            <a href="{{ route('employees.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('employees.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::ShiftTemplates)
            <a href="{{ route('shifts.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('shifts.*') && ! request()->routeIs('employee-shifts.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::EmployeeShifts)
            <a href="{{ route('employee-shifts.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('employee-shifts.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Holidays)
            <a href="{{ route('holidays.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('holidays.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Reports)
            <a href="{{ route('reports.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('reports.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::ActivityLogs)
            <a href="{{ route('activity-logs.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('activity-logs.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Announcements)
            <a href="{{ route('announcements.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('announcements.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Users)
            <a href="{{ route('users.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('users.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Roles)
            <a href="{{ route('roles.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('roles.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break

        @case(SidebarNavItem::Settings)
            <a href="{{ route('settings.index') }}" class="{{ $linkClass }}{{ $nestedClass }} {{ request()->routeIs('settings.*') ? $activeClass : $inactiveClass }}">{{ __($item->navLabelKey()) }}</a>
            @break
    @endswitch
@endif
