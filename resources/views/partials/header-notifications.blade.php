@php
    use App\Enums\Permission;
    use App\Enums\SidebarNavItem;

    $user = auth()->user();
    $canSeeLeave = collect(SidebarNavItem::leaveApprovalItems())
        ->contains(fn (SidebarNavItem $item) => $sidebar->visible($user, $item));
    $canSeeOwnLeave = $user->employee !== null && (
        collect(SidebarNavItem::leaveHistoryItems())->contains(fn (SidebarNavItem $item) => $sidebar->visible($user, $item))
        || $user->hasPermission(Permission::LeaveRequest)
        || $user->hasPermission(Permission::LeaveViewOwn)
    );
    $canSeePayroll = $sidebar->visible($user, SidebarNavItem::Payroll)
        && $user->hasPermission(Permission::PayrollManage);

    $approvalBreakdown = $pendingLeaveApprovalBreakdown ?? ['cuti' => 0, 'izin' => 0, 'lembur' => 0, 'total' => 0];
    $pendingOwnBreakdown = $pendingOwnLeaveBreakdown ?? ['cuti' => 0, 'izin' => 0, 'lembur' => 0, 'total' => 0];
    $ownStatusModules = $unreadOwnLeaveStatusModuleBreakdown ?? [
        'cuti' => ['approved' => 0, 'rejected' => 0, 'total' => 0],
        'izin' => ['approved' => 0, 'rejected' => 0, 'total' => 0],
        'lembur' => ['approved' => 0, 'rejected' => 0, 'total' => 0],
        'total' => 0,
    ];
    $payrollCount = $canSeePayroll ? (int) ($pendingPayrollSignatureCount ?? 0) : 0;
    $faceEnrollmentNeeded = ($needsFaceEnrollment ?? false) ? 1 : 0;

    $leaveCount = $canSeeLeave ? (int) ($approvalBreakdown['total'] ?? 0) : 0;
    $pendingOwnCount = $canSeeOwnLeave ? (int) ($pendingOwnBreakdown['total'] ?? 0) : 0;
    $ownStatusCount = $canSeeOwnLeave ? (int) ($ownStatusModules['total'] ?? 0) : 0;
    $totalCount = $leaveCount + $pendingOwnCount + $ownStatusCount + $payrollCount + $faceEnrollmentNeeded;

    $modules = [
        'cuti' => [
            'label' => __('nav.section_leave_cuti'),
            'chip' => 'leave',
            'approval_item' => SidebarNavItem::LeaveCutiApproval,
            'history_item' => SidebarNavItem::LeaveCutiHistory,
            'approval_title' => __('nav.leave_cuti_approval'),
        ],
        'izin' => [
            'label' => __('nav.section_leave_izin'),
            'chip' => 'permission',
            'approval_item' => SidebarNavItem::LeaveIzinApproval,
            'history_item' => SidebarNavItem::LeaveIzinHistory,
            'approval_title' => __('nav.leave_izin_approval'),
        ],
        'lembur' => [
            'label' => __('nav.section_leave_lembur'),
            'chip' => 'overtime',
            'approval_item' => SidebarNavItem::LeaveLemburApproval,
            'history_item' => SidebarNavItem::LeaveLemburHistory,
            'approval_title' => __('nav.leave_lembur_approval'),
        ],
    ];

    $canSeeNotifications = $canSeeLeave || $canSeeOwnLeave || $canSeePayroll || $faceEnrollmentNeeded;
@endphp

@if($canSeeNotifications)
    <div class="header-notifications" data-header-notifications>
        <button
            type="button"
            class="header-notifications__trigger"
            data-header-notifications-trigger
            aria-expanded="false"
            aria-haspopup="menu"
            aria-label="{{ $totalCount > 0 ? __('app.notifications_title', ['count' => $totalCount]) : __('app.notifications_title_plain') }}"
        >
            <span class="header-notifications__icon-wrap">
                <svg class="header-notifications__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                @if($totalCount > 0)
                    <span class="header-notifications__count leave-badge-pulse">
                        {{ $totalCount > 99 ? '99+' : $totalCount }}
                    </span>
                @endif
            </span>
        </button>

        <div class="header-notifications__panel" data-header-notifications-panel role="menu" hidden>
            <div class="header-notifications__panel-head">
                <div>
                    <p class="header-notifications__panel-title">{{ __('app.notifications_title_plain') }}</p>
                    <p class="header-notifications__panel-subtitle">
                        @if($totalCount > 0)
                            {{ __('app.notifications_unread', ['count' => $totalCount]) }}
                        @else
                            {{ __('app.notifications_empty') }}
                        @endif
                    </p>
                </div>
                @if($totalCount > 0)
                    <span class="header-notifications__panel-total">{{ $totalCount }}</span>
                @endif
            </div>

            <div class="header-notifications__list">
                @if($totalCount === 0)
                    <p class="header-notifications__empty">{{ __('app.notifications_empty_detail') }}</p>
                @endif

                @if($faceEnrollmentNeeded)
                    <a
                        href="{{ route('profile.edit') }}#face-enrollment"
                        class="header-notifications__item"
                        role="menuitem"
                    >
                        <span class="header-notifications__item-icon header-notifications__item-icon--face">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        <span class="header-notifications__item-body">
                            <span class="header-notifications__item-title">{{ __('pages.profile.face_notification_title') }}</span>
                            <span class="header-notifications__item-meta">{{ __('pages.profile.face_notification_message') }}</span>
                        </span>
                        <span class="header-notifications__item-side">
                            <span class="header-notifications__item-count">!</span>
                            <svg class="header-notifications__item-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </a>
                @endif

                @foreach($modules as $category => $module)
                    @php
                        $approvalCount = (int) ($approvalBreakdown[$category] ?? 0);
                        $pendingOwnModuleCount = (int) ($pendingOwnBreakdown[$category] ?? 0);
                        $ownStatusModule = $ownStatusModules[$category] ?? ['approved' => 0, 'rejected' => 0, 'total' => 0];
                        $ownStatusModuleCount = (int) ($ownStatusModule['total'] ?? 0);
                        $chipClass = 'dashboard-notif-chip--'.$module['chip'];
                    @endphp

                    @if($canSeeLeave && $approvalCount > 0)
                        <a
                            href="{{ route('leave-approvals.'.$category, ['status' => 'pending']) }}"
                            class="header-notifications__item"
                            role="menuitem"
                        >
                            <span class="header-notifications__item-icon header-notifications__item-icon--leave">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </span>
                            <span class="header-notifications__item-body">
                                <span class="header-notifications__item-title">{{ $module['approval_title'] }}</span>
                                <span class="header-notifications__item-meta">{{ __('app.notifications_module_approval_pending', ['count' => $approvalCount]) }}</span>
                                <span class="header-notifications__item-chips">
                                    <span @class(['dashboard-notif-chip', $chipClass])>{{ $module['label'] }} {{ $approvalCount }}</span>
                                </span>
                            </span>
                            <span class="header-notifications__item-side">
                                <span class="header-notifications__item-count">{{ $approvalCount }}</span>
                                <svg class="header-notifications__item-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </a>
                    @endif

                    @if($canSeeOwnLeave && $sidebar->visible($user, $module['history_item']) && $pendingOwnModuleCount > 0)
                        <a
                            href="{{ route('leaves.index', ['category' => $category]) }}"
                            class="header-notifications__item"
                            role="menuitem"
                        >
                            <span class="header-notifications__item-icon header-notifications__item-icon--leave">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <span class="header-notifications__item-body">
                                <span class="header-notifications__item-title">{{ $module['label'] }}</span>
                                <span class="header-notifications__item-meta">{{ __('app.notifications_module_own_pending', ['count' => $pendingOwnModuleCount]) }}</span>
                                <span class="header-notifications__item-chips">
                                    <span @class(['dashboard-notif-chip', $chipClass])>{{ $module['label'] }} {{ $pendingOwnModuleCount }}</span>
                                </span>
                            </span>
                            <span class="header-notifications__item-side">
                                <span class="header-notifications__item-count">{{ $pendingOwnModuleCount }}</span>
                                <svg class="header-notifications__item-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </a>
                    @endif

                    @if($canSeeOwnLeave && $sidebar->visible($user, $module['history_item']) && $ownStatusModuleCount > 0)
                        <a
                            href="{{ route('leaves.index', ['category' => $category, 'ack' => $category]) }}"
                            class="header-notifications__item"
                            role="menuitem"
                        >
                            <span class="header-notifications__item-icon header-notifications__item-icon--leave">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </span>
                            <span class="header-notifications__item-body">
                                <span class="header-notifications__item-title">{{ $module['label'] }}</span>
                                <span class="header-notifications__item-meta">{{ __('app.notifications_module_status_pending', ['count' => $ownStatusModuleCount]) }}</span>
                                <span class="header-notifications__item-chips">
                                    @if(($ownStatusModule['approved'] ?? 0) > 0)
                                        <span class="dashboard-notif-chip dashboard-notif-chip--approved">{{ __('app.approved') }} {{ $ownStatusModule['approved'] }}</span>
                                    @endif
                                    @if(($ownStatusModule['rejected'] ?? 0) > 0)
                                        <span class="dashboard-notif-chip dashboard-notif-chip--rejected">{{ __('app.rejected') }} {{ $ownStatusModule['rejected'] }}</span>
                                    @endif
                                </span>
                            </span>
                            <span class="header-notifications__item-side">
                                <span class="header-notifications__item-count">{{ $ownStatusModuleCount }}</span>
                                <svg class="header-notifications__item-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </span>
                        </a>
                    @endif
                @endforeach

                @if($payrollCount > 0)
                    <a
                        href="{{ route('payrolls.index') }}"
                        class="header-notifications__item"
                        role="menuitem"
                    >
                        <span class="header-notifications__item-icon header-notifications__item-icon--signature">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </span>
                        <span class="header-notifications__item-body">
                            <span class="header-notifications__item-title">{{ __('pages.dashboard.signature_approval_title') }}</span>
                            <span class="header-notifications__item-meta">{{ __('pages.dashboard.signature_approval_pending', ['count' => $payrollCount]) }}</span>
                        </span>
                        <span class="header-notifications__item-side">
                            <span class="header-notifications__item-count">{{ $payrollCount }}</span>
                            <svg class="header-notifications__item-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
