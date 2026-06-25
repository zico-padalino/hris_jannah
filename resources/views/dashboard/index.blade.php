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

    @include('partials.dashboard-approver-notifications')

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

    <div class="mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-campfire-1 via-campfire-2 to-campfire-3 p-6 text-white shadow-xl shadow-campfire-1/25 sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-campfire-4">{{ $greeting }},</p>
                <h2 class="mt-1 text-2xl font-bold sm:text-3xl">{{ auth()->user()->name }}</h2>
                <p class="mt-2 max-w-xl text-sm text-campfire-4/90">
                    {{ __('pages.dashboard.summary', ['role' => auth()->user()->role->label()]) }}
                </p>
            </div>
            <div class="grid grid-cols-1 gap-3 min-[380px]:grid-cols-3 sm:gap-4">
                <div class="rounded-xl bg-white/10 px-4 py-3 text-center backdrop-blur-sm ring-1 ring-white/20">
                    <p class="text-2xl font-bold">{{ $stats['attendances_today'] }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-campfire-4">Absensi hari ini</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3 text-center backdrop-blur-sm ring-1 ring-white/20">
                    <p class="text-2xl font-bold text-campfire-4">{{ $stats['on_time_today'] }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-campfire-4">Tepat waktu</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3 text-center backdrop-blur-sm ring-1 ring-white/20">
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
                    'group relative overflow-hidden rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                    'dashboard-pending-card border-amber-300 bg-gradient-to-br from-amber-50 to-orange-100 ring-2 ring-amber-200/60 leave-badge-pulse dark:from-amber-950/50 dark:to-orange-950/40 dark:ring-amber-700/40' => $pendingLeaveApprovalCount > 0,
                    'dashboard-pending-card--idle border-slate-200/80 bg-amber-50 dark:bg-slate-800/50' => $pendingLeaveApprovalCount === 0,
                ])
            >
                @if($pendingLeaveApprovalCount > 0)
                    <span class="absolute right-4 top-4 flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-500 opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-600"></span>
                    </span>
                @endif
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p @class(['text-sm font-medium', 'text-amber-800 dark:text-amber-200' => $pendingLeaveApprovalCount > 0, 'text-slate-600 dark:text-slate-400' => $pendingLeaveApprovalCount === 0])>
                            Pengajuan Menunggu
                        </p>
                        <p @class(['mt-2 text-3xl font-bold tracking-tight', 'text-amber-700 dark:text-amber-300' => $pendingLeaveApprovalCount > 0, 'text-slate-400 dark:text-slate-500' => $pendingLeaveApprovalCount === 0])>
                            {{ $pendingLeaveApprovalCount }}
                        </p>
                        @if($pendingLeaveApprovalCount > 0)
                            <span class="mt-2 inline-flex rounded-full bg-amber-500 px-2.5 py-0.5 text-xs font-semibold text-white">
                                Perlu diproses
                            </span>
                        @else
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Tidak ada antrian</p>
                        @endif
                    </div>
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                </div>
            </a>
        @endperm
    </div>

    @include('partials.dashboard-attendance-chart')
@endsection
