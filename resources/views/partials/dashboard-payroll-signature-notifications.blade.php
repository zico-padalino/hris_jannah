@perm('payroll.manage')
    @php
        $totalPending = $payrollSignatureNotifications['count'] ?? 0;
        $recent = $payrollSignatureNotifications['recent'] ?? collect();
    @endphp

    <div @class([
        'panel mb-6 overflow-hidden',
        'app-notification-panel--active leave-badge-pulse' => $totalPending > 0,
    ])>
        <div class="dashboard-section-head flex flex-col gap-4 border-b-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-start gap-3">
                <span @class([
                    'app-notification-icon',
                    'leave-badge-pulse' => $totalPending > 0,
                    'app-notification-icon--idle' => $totalPending === 0,
                ])>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    @if($totalPending > 0)
                        <span class="app-notification-banner__count">
                            {{ $totalPending > 99 ? '99+' : $totalPending }}
                        </span>
                    @endif
                </span>
                <div>
                    <h2 class="dashboard-section-title">{{ __('pages.dashboard.signature_approval_title') }}</h2>
                    <p class="dashboard-section-subtitle mt-0.5">
                        @if($totalPending > 0)
                            {{ __('pages.dashboard.signature_approval_pending', ['count' => $totalPending]) }}
                        @else
                            {{ __('pages.dashboard.signature_approval_clear') }}
                        @endif
                    </p>
                    @if($totalPending > 0)
                        <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold">
                            <span class="app-status-pending">{{ __('pages.payroll_slip.request_signature') }} {{ $totalPending }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <a href="{{ route('payrolls.index') }}" class="btn-primary w-full sm:w-auto">
                {{ __('pages.dashboard.signature_approval_process') }}
            </a>
        </div>

        @if($recent->isNotEmpty())
            <div class="bg-slate-50 px-4 py-4 dark:bg-slate-900/40 sm:px-6">
                <h3 class="dashboard-section-subtitle mb-3 text-sm uppercase tracking-wide">{{ __('pages.dashboard.signature_approval_recent') }}</h3>
                <ul class="space-y-2">
                    @foreach($recent as $signature)
                        @php
                            $item = $signature->payrollItem;
                            $period = $item->payrollPeriod;
                        @endphp
                        <li>
                            <a
                                href="{{ route('payrolls.show', $period) }}"
                                class="flex flex-col gap-2 rounded-lg border-2 border-slate-200 bg-white px-4 py-3 transition hover:border-teal-300 dark:border-slate-600 dark:bg-slate-800/60 dark:hover:border-teal-500 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-900 dark:text-slate-100">{{ $item->employee->name }}</p>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                                        {{ $period->name }}
                                        · {{ $item->employee->employee_number }}
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="rounded-md border-2 border-slate-300 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-800">
                                        {{ __('pages.payroll_slip.title') }}
                                    </span>
                                    <span class="app-status-pending">
                                        {{ __('pages.payroll_slip.signature_pending') }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endperm
