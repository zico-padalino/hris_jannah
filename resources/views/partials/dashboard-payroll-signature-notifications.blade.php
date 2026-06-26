@perm('payroll.manage')
    @php
        $totalPending = $payrollSignatureNotifications['count'] ?? 0;
        $recent = $payrollSignatureNotifications['recent'] ?? collect();
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    @if($totalPending > 0)
                        <span class="dashboard-notif-card__count">
                            {{ $totalPending > 99 ? '99+' : $totalPending }}
                        </span>
                    @endif
                </span>
                <div class="dashboard-notif-card__body">
                    <h2 class="dashboard-notif-card__title">{{ __('pages.dashboard.signature_approval_title') }}</h2>
                    <p class="dashboard-notif-card__subtitle">
                        @if($totalPending > 0)
                            {{ __('pages.dashboard.signature_approval_pending', ['count' => $totalPending]) }}
                        @else
                            {{ __('pages.dashboard.signature_approval_clear') }}
                        @endif
                    </p>
                    @if($totalPending > 0)
                        <div class="dashboard-notif-card__chips">
                            <span class="dashboard-notif-chip dashboard-notif-chip--leave">{{ __('pages.payroll_slip.request_signature') }} {{ $totalPending }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('payrolls.index') }}" class="btn-primary dashboard-notif-card__btn shrink-0">
                {{ __('pages.dashboard.signature_approval_process') }}
            </a>
        </div>

        @if($recent->isNotEmpty())
            <div class="dashboard-notif-card__recent">
                <p class="dashboard-notif-card__recent-label">{{ __('pages.dashboard.signature_approval_recent') }}</p>
                <ul class="dashboard-notif-card__recent-list">
                    @foreach($recent as $signature)
                        @php
                            $item = $signature->payrollItem;
                            $period = $item->payrollPeriod;
                        @endphp
                        <li>
                            <a href="{{ route('payrolls.show', $period) }}" class="dashboard-notif-recent-item">
                                <div class="min-w-0">
                                    <p class="dashboard-notif-recent-item__name">{{ $item->employee->name }}</p>
                                    <p class="dashboard-notif-recent-item__meta">
                                        {{ $period->name }}
                                        · {{ $item->employee->employee_number }}
                                    </p>
                                </div>
                                <div class="dashboard-notif-recent-item__badges">
                                    <span class="dashboard-notif-recent-item__type">{{ __('pages.payroll_slip.title') }}</span>
                                    <span class="app-status-pending app-status-pending--compact">{{ __('pages.payroll_slip.signature_pending') }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endperm
