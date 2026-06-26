@perm('leave.approve')
    @php
        $breakdown = $approverNotifications['breakdown'] ?? ['cuti' => 0, 'izin' => 0, 'lembur' => 0, 'total' => 0];
        $recent = $approverNotifications['recent'] ?? collect();
        $totalPending = $breakdown['total'] ?? 0;
    @endphp

    <div @class([
        'panel dashboard-notif-card overflow-hidden',
        'app-notification-panel--active leave-badge-pulse' => $totalPending > 0,
    ])>
        <div @class([
            'dashboard-notif-card__head',
            'dashboard-notif-card__head--bordered' => $recent->isNotEmpty(),
        ])>
            <div class="dashboard-notif-card__main">
                <span @class([
                    'dashboard-notif-card__icon',
                    'leave-badge-pulse' => $totalPending > 0,
                    'dashboard-notif-card__icon--idle' => $totalPending === 0,
                ])>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($totalPending > 0)
                        <span class="dashboard-notif-card__count">
                            {{ $totalPending > 99 ? '99+' : $totalPending }}
                        </span>
                    @endif
                </span>
                <div class="dashboard-notif-card__body">
                    <h2 class="dashboard-notif-card__title">{{ __('pages.dashboard.approval_title') }}</h2>
                    <p class="dashboard-notif-card__subtitle">
                        @if($totalPending > 0)
                            {{ __('pages.dashboard.approval_pending', ['count' => $totalPending]) }}
                        @else
                            {{ __('pages.dashboard.approval_clear') }}
                        @endif
                    </p>
                    @if($totalPending > 0)
                        <div class="dashboard-notif-card__chips">
                            <span class="dashboard-notif-chip dashboard-notif-chip--leave">{{ __('leave.category_leave') }} {{ $breakdown['cuti'] ?? 0 }}</span>
                            <span class="dashboard-notif-chip dashboard-notif-chip--permission">{{ __('leave.category_permission') }} {{ $breakdown['izin'] ?? 0 }}</span>
                            <span class="dashboard-notif-chip dashboard-notif-chip--overtime">{{ __('leave.category_overtime') }} {{ $breakdown['lembur'] ?? 0 }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('leave-approvals.index', ['status' => 'pending']) }}" class="btn-primary dashboard-notif-card__btn shrink-0">
                {{ __('pages.dashboard.approval_process') }}
            </a>
        </div>

        @if($recent->isNotEmpty())
            <div class="dashboard-notif-card__recent">
                <p class="dashboard-notif-card__recent-label">{{ __('pages.dashboard.approval_recent') }}</p>
                <ul class="dashboard-notif-card__recent-list">
                    @foreach($recent as $leave)
                        <li>
                            <a href="{{ route('leave-approvals.index', ['status' => 'pending']) }}" class="dashboard-notif-recent-item">
                                <div class="min-w-0">
                                    <p class="dashboard-notif-recent-item__name">{{ $leave->employee->name }}</p>
                                    <p class="dashboard-notif-recent-item__meta">
                                        {{ $leave->branch->name }}
                                        · {{ $leave->start_date->format('d/m/Y') }}
                                        @if(! $leave->start_date->isSameDay($leave->end_date))
                                            – {{ $leave->end_date->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>
                                <div class="dashboard-notif-recent-item__badges">
                                    <span class="dashboard-notif-recent-item__type">{{ $leave->type->label() }}</span>
                                    <span class="app-status-pending app-status-pending--compact">{{ __('app.pending') }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endperm
