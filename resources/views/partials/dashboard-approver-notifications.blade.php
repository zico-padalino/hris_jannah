@perm('leave.approve')
    @php
        $breakdown = $approverNotifications['breakdown'] ?? ['cuti' => 0, 'izin' => 0, 'lembur' => 0, 'total' => 0];
        $recent = $approverNotifications['recent'] ?? collect();
        $totalPending = $breakdown['total'] ?? 0;
    @endphp

    <div @class([
        'panel mb-6 overflow-hidden',
        'ring-2 ring-amber-300 leave-badge-pulse' => $totalPending > 0,
    ])>
        <div class="dashboard-section-head flex flex-col gap-4 border-b-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-start gap-3">
                <span @class([
                    'relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-md',
                    'bg-amber-500 leave-badge-pulse' => $totalPending > 0,
                    'bg-teal-700' => $totalPending === 0,
                ])>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($totalPending > 0)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-white">
                            {{ $totalPending > 99 ? '99+' : $totalPending }}
                        </span>
                    @endif
                </span>
                <div>
                    <h2 class="dashboard-section-title">{{ __('pages.dashboard.approval_title') }}</h2>
                    <p class="dashboard-section-subtitle mt-0.5">
                        @if($totalPending > 0)
                            {{ __('pages.dashboard.approval_pending', ['count' => $totalPending]) }}
                        @else
                            {{ __('pages.dashboard.approval_clear') }}
                        @endif
                    </p>
                    <div class="mt-2 flex flex-wrap gap-2 text-xs font-bold">
                        <span class="rounded-md bg-emerald-100 px-2 py-1 text-emerald-800">{{ __('leave.category_leave') }} {{ $breakdown['cuti'] ?? 0 }}</span>
                        <span class="rounded-md bg-sky-100 px-2 py-1 text-sky-800">{{ __('leave.category_permission') }} {{ $breakdown['izin'] ?? 0 }}</span>
                        <span class="rounded-md bg-violet-100 px-2 py-1 text-violet-800">{{ __('leave.category_overtime') }} {{ $breakdown['lembur'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('leave-approvals.index', ['status' => 'pending']) }}" class="btn-primary w-full sm:w-auto">
                {{ __('pages.dashboard.approval_process') }}
            </a>
        </div>

        @if($recent->isNotEmpty())
            <div class="bg-slate-50 px-4 py-4 dark:bg-slate-900/40 sm:px-6">
                <h3 class="dashboard-section-subtitle mb-3 text-sm uppercase tracking-wide">{{ __('pages.dashboard.approval_recent') }}</h3>
                <ul class="space-y-2">
                    @foreach($recent as $leave)
                        <li>
                            <a
                                href="{{ route('leave-approvals.index', ['status' => 'pending']) }}"
                                class="flex flex-col gap-2 rounded-lg border-2 border-slate-200 bg-white px-4 py-3 transition hover:border-teal-300 dark:border-slate-600 dark:bg-slate-800/60 dark:hover:border-teal-500 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-slate-900 dark:text-slate-100">{{ $leave->employee->name }}</p>
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                                        {{ $leave->branch->name }}
                                        · {{ $leave->start_date->format('d/m/Y') }}
                                        @if(! $leave->start_date->isSameDay($leave->end_date))
                                            – {{ $leave->end_date->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="rounded-md border-2 border-slate-300 bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-800">
                                        {{ $leave->type->label() }}
                                    </span>
                                    <span class="rounded-md bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900 ring-1 ring-amber-300">
                                        {{ __('app.pending') }}
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
