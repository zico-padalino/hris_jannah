@extends('layouts.app')

@section('title', __('pages.dashboard.title'))
@section('subtitle')
    @include('partials.header-live-clock')
@endsection

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

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="panel-table lg:col-span-2">
            <div class="dashboard-section-head flex flex-col gap-3 border-b-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <h2 class="dashboard-section-title">Absensi Terbaru</h2>
                    <p class="dashboard-section-subtitle">10 record absensi terakhir</p>
                </div>
                <a href="{{ route('attendances.index') }}" class="link-action">Lihat semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-readable min-w-full">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pegawai</th>
                            <th class="cell-absensi-header">Absensi</th>
                            <th class="cell-status-header">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendances as $attendance)
                            <tr>
                                <td class="whitespace-nowrap">
                                    <p class="cell-primary">{{ $attendance->attended_at->format('d/m/Y') }}</p>
                                    <p class="cell-secondary">{{ $attendance->attended_at->format('H:i') }} WIB</p>
                                </td>
                                <td>
                                    <p class="cell-primary">{{ $attendance->employee->name }}</p>
                                    <p class="cell-secondary">{{ $attendance->branch->name }}</p>
                                </td>
                                <td class="cell-absensi">
                                    @include('partials.attendance-time-entry', ['attendance' => $attendance, 'showTime' => false])
                                </td>
                                <td class="cell-status">
                                    @include('partials.attendance-status-entry', ['attendance' => $attendance])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                                            <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                            </svg>
                                        </div>
                                        <p class="font-medium text-slate-700 dark:text-slate-200">Belum ada absensi</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Data akan muncul setelah pegawai absen.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-quick-panel rounded-2xl border p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="dashboard-section-title text-lg">Aksi Cepat</h2>
                <p class="dashboard-section-subtitle text-xs">Shortcut menu yang sering dipakai</p>
            </div>
            <div class="space-y-2.5">
                @perm('attendance.scan')
                    <a href="{{ route('attendance.scan') }}" class="group flex items-center gap-3 rounded-xl bg-gradient-to-r from-campfire-1 to-campfire-2 px-4 py-3.5 text-sm font-medium text-white shadow-sm transition hover:from-campfire-1 hover:to-campfire-3 hover:shadow-md">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                        </span>
                        <span class="flex-1">Scan Absensi</span>
                        <svg class="h-4 w-4 opacity-70 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                @endperm

                <a href="{{ route('attendances.index') }}" class="dashboard-quick-link group flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-medium transition hover:border-teal-200 hover:bg-teal-50/50 dark:hover:bg-teal-950/30">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-teal-100 group-hover:text-teal-700 dark:bg-slate-800 dark:text-slate-300 dark:group-hover:bg-teal-900/50 dark:group-hover:text-teal-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75V18zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </span>
                    <span class="flex-1">Riwayat Absensi</span>
                </a>

                @perm('leave.approve')
                    <a
                        href="{{ route('leave-approvals.index', ['status' => 'pending']) }}"
                        @class([
                            'group flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition',
                            'border-2 border-amber-300 bg-amber-50 text-amber-900 hover:bg-amber-100 leave-badge-pulse' => $pendingLeaveApprovalCount > 0,
                            'border border-slate-200 text-slate-700 hover:border-amber-200 hover:bg-amber-50/50 dark:border-slate-600 dark:text-slate-200 dark:hover:border-amber-500/50 dark:hover:bg-amber-950/30' => $pendingLeaveApprovalCount === 0,
                        ])
                    >
                        <span @class([
                            'flex h-9 w-9 items-center justify-center rounded-lg',
                            'bg-amber-200 text-amber-800' => $pendingLeaveApprovalCount > 0,
                            'bg-slate-100 text-slate-600 group-hover:bg-amber-100 group-hover:text-amber-700 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:bg-amber-900/50 dark:group-hover:text-amber-200' => $pendingLeaveApprovalCount === 0,
                        ])>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <span class="flex-1">Approval Pengajuan</span>
                        @include('partials.count-badge', [
                            'count' => $pendingLeaveApprovalCount,
                            'variant' => 'pill',
                            'label' => $pendingLeaveApprovalCount > 0 ? 'baru' : null,
                            'pulse' => $pendingLeaveApprovalCount > 0,
                        ])
                    </a>
                @endperm

                @if(auth()->user()->employee && (auth()->user()->hasPermission(\App\Enums\Permission::LeaveRequest) || auth()->user()->hasPermission(\App\Enums\Permission::LeaveViewOwn)))
                    <a
                        href="{{ route('leaves.index') }}"
                        @class([
                            'group flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition',
                            'border-2 border-sky-300 bg-sky-50 text-sky-900 hover:bg-sky-100' => $pendingOwnLeaveCount > 0,
                            'border border-slate-200 text-slate-700 hover:border-sky-200 hover:bg-sky-50/50 dark:border-slate-600 dark:text-slate-200 dark:hover:border-sky-500/50 dark:hover:bg-sky-950/30' => $pendingOwnLeaveCount === 0,
                        ])
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600 group-hover:bg-sky-100 group-hover:text-sky-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                        </span>
                        <span class="flex-1">Riwayat Pengajuan</span>
                        @if($pendingOwnLeaveCount > 0)
                            @include('partials.count-badge', [
                                'count' => $pendingOwnLeaveCount,
                                'variant' => 'pill',
                                'label' => 'proses',
                                'pulse' => true,
                            ])
                        @endif
                    </a>
                @endif

                @if(auth()->user()->role->value !== 'employee')
                    <div class="my-3 border-t border-slate-100 pt-3 dark:border-slate-700">
                        <p class="mb-2 px-1 text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Master Data</p>
                        <div class="space-y-2">
                            <a href="{{ route('employees.create') }}" class="dashboard-quick-link group flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm transition hover:border-teal-200 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-hover:text-teal-700 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:text-teal-300">+</span>
                                Tambah Pegawai
                            </a>
                            <a href="{{ route('branches.create') }}" class="dashboard-quick-link group flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm transition hover:border-teal-200 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-hover:text-teal-700 dark:bg-slate-700 dark:text-slate-300 dark:group-hover:text-teal-300">+</span>
                                Tambah Cabang
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
