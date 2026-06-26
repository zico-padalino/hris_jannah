@extends('layouts.app')

@section('title', __('pages.dashboard.title'))

@section('content')
    @php
        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 11 => __('pages.dashboard.greeting_morning'),
            $hour < 15 => __('pages.dashboard.greeting_afternoon'),
            $hour < 18 => __('pages.dashboard.greeting_evening'),
            default => __('pages.dashboard.greeting_night'),
        };
    @endphp

    @include('partials.dashboard-payroll-signature-notifications')

    @include('partials.dashboard-face-enrollment-notification')

    @include('partials.dashboard-announcements', ['announcements' => $announcements])

    @if(auth()->user()->employee && $pendingOwnLeaveCount > 0)
        @include('partials.leave-alert-banner', [
            'count' => $pendingOwnLeaveCount,
            'title' => __('pages.dashboard.own_request_title'),
            'message' => __('pages.dashboard.own_request_message', ['count' => $pendingOwnLeaveCount]),
            'href' => route('leaves.index'),
            'buttonLabel' => __('pages.dashboard.view_status'),
        ])
    @endif

    <div class="dashboard-welcome mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-campfire-1 via-campfire-2 to-campfire-3 p-6 text-white shadow-xl shadow-campfire-1/25 sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-campfire-4">{{ $greeting }},</p>
                <h2 class="mt-1 text-2xl font-bold sm:text-3xl">{{ auth()->user()->name }}</h2>
                <p class="mt-2 max-w-xl text-sm text-campfire-4/90">
                    {{ __('pages.dashboard.summary', ['role' => auth()->user()->role->label()]) }}
                </p>
            </div>
            <div class="grid grid-cols-1 gap-3 min-[380px]:grid-cols-3 sm:gap-4">
                <div class="dashboard-stat-compact rounded-xl border border-white/25 bg-black/10 px-4 py-3 text-center">
                    <p class="text-2xl font-bold">{{ $stats['attendances_today'] }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-campfire-4">Absensi hari ini</p>
                </div>
                <div class="dashboard-stat-compact rounded-xl border border-white/25 bg-black/10 px-4 py-3 text-center">
                    <p class="text-2xl font-bold text-campfire-4">{{ $stats['on_time_today'] }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-campfire-4">Tepat waktu</p>
                </div>
                <div class="dashboard-stat-compact rounded-xl border border-white/25 bg-black/10 px-4 py-3 text-center">
                    <p class="text-2xl font-bold text-white">{{ $stats['late_today'] }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-campfire-4">Terlambat</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if(auth()->user()->role->value !== 'employee')
            @include('partials.dashboard-stat-card', [
                'label' => 'Cabang Aktif',
                'value' => number_format($stats['branches'], 0, ',', '.'),
                'tone' => 'teal',
                'hint' => 'Unit operasional terdaftar',
                'href' => route('branches.index'),
                'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5M3.75 9.75v10.5m7.5-10.5v10.5m7.5-10.5v10.5M4.5 4.5h15a1.5 1.5 0 011.5 1.5v3a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 9V6a1.5 1.5 0 011.5-1.5z" /></svg>',
            ])
            @include('partials.dashboard-stat-card', [
                'label' => 'Pegawai Aktif',
                'value' => number_format($stats['employees'], 0, ',', '.'),
                'tone' => 'sky',
                'hint' => 'Tenaga kerja aktif',
                'href' => route('employees.index'),
                'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>',
            ])
        @endif

        @include('partials.dashboard-stat-card', [
            'label' => 'Absensi Hari Ini',
            'value' => number_format($stats['attendances_today'], 0, ',', '.'),
            'tone' => 'emerald',
            'hint' => $stats['on_time_today'].' tepat waktu · '.$stats['late_today'].' terlambat',
            'href' => route('attendances.index', ['date' => today()->toDateString()]),
            'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>',
        ])

        @include('partials.dashboard-stat-card', [
            'label' => 'Invalid Hari Ini',
            'value' => number_format($stats['invalid_today'], 0, ',', '.'),
            'tone' => $stats['invalid_today'] > 0 ? 'red' : 'violet',
            'hint' => $stats['invalid_today'] > 0 ? 'Perlu ditinjau segera' : 'Tidak ada masalah verifikasi',
            'href' => route('attendances.index', ['date' => today()->toDateString(), 'status' => 'invalid_face']),
            'icon' => '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>',
        ])

        @perm('leave.approve')
            <a
                href="{{ route('leave-approvals.index', ['status' => 'pending']) }}"
                @class([
                    'group relative overflow-hidden rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dashboard-pending-card',
                    'dashboard-pending-card--active leave-badge-pulse' => $pendingLeaveApprovalCount > 0,
                    'dashboard-pending-card--idle' => $pendingLeaveApprovalCount === 0,
                ])
            >
                @if($pendingLeaveApprovalCount > 0)
                    <span class="absolute right-4 top-4 app-notification-dot">
                        <span class="app-notification-dot__ping"></span>
                        <span class="app-notification-dot__core"></span>
                    </span>
                @endif
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-pending-card__label text-sm font-medium">
                            Pengajuan Menunggu
                        </p>
                        <p class="dashboard-pending-card__value mt-2 text-3xl font-bold tracking-tight">
                            {{ $pendingLeaveApprovalCount }}
                        </p>
                        @if($pendingLeaveApprovalCount > 0)
                            <span class="mt-2 app-pending-chip">
                                Perlu diproses
                            </span>
                        @else
                            <p class="dashboard-pending-card__hint mt-2 text-xs">Tidak ada antrian</p>
                        @endif
                    </div>
                    <div class="dashboard-pending-card__icon-wrap flex h-11 w-11 shrink-0 items-center justify-center rounded-xl">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                </div>
            </a>
        @endperm

        @perm('payroll.manage')
            <a
                href="{{ route('payrolls.index') }}"
                @class([
                    'group relative overflow-hidden rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dashboard-pending-card',
                    'dashboard-pending-card--active leave-badge-pulse' => $pendingPayrollSignatureCount > 0,
                    'dashboard-pending-card--idle' => $pendingPayrollSignatureCount === 0,
                ])
            >
                @if($pendingPayrollSignatureCount > 0)
                    <span class="absolute right-4 top-4 app-notification-dot">
                        <span class="app-notification-dot__ping"></span>
                        <span class="app-notification-dot__core"></span>
                    </span>
                @endif
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="dashboard-pending-card__label text-sm font-medium">
                            {{ __('pages.dashboard.signature_approval_title') }}
                        </p>
                        <p class="dashboard-pending-card__value mt-2 text-3xl font-bold tracking-tight">
                            {{ $pendingPayrollSignatureCount }}
                        </p>
                        @if($pendingPayrollSignatureCount > 0)
                            <span class="mt-2 app-pending-chip">
                                {{ __('pages.dashboard.needs_processing') }}
                            </span>
                        @else
                            <p class="dashboard-pending-card__hint mt-2 text-xs">{{ __('pages.dashboard.signature_approval_clear') }}</p>
                        @endif
                    </div>
                    <div class="dashboard-pending-card__icon-wrap flex h-11 w-11 shrink-0 items-center justify-center rounded-xl">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                </div>
            </a>
        @endperm
    </div>

    @include('partials.dashboard-attendance-chart')
@endsection
