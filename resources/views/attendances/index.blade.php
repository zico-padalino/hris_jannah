@extends('layouts.app')

@section('title', __('pages.attendance_history.title'))
@section('subtitle', __('pages.attendance_history.subtitle'))

@section('content')
    <div class="min-w-0">
    @php
        $hasFilters = request()->filled('branch_id') || request()->filled('date') || request()->filled('status');
    @endphp

    <div class="filter-bar min-w-0">
        <div class="flex min-w-0 flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <form method="GET" class="grid min-w-0 flex-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @if(auth()->user()->role->value !== 'employee')
                    <label class="block min-w-0">
                        <span class="form-label">Cabang</span>
                        <select name="branch_id" class="w-full min-w-0 max-w-full">
                            <option value="">Semua cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <label class="block min-w-0">
                    <span class="form-label">Tanggal</span>
                    <div class="form-date-wrap">
                        <input type="date" name="date" value="{{ request('date') }}" lang="id" class="form-date-input w-full min-w-0 max-w-full">
                    </div>
                </label>

                <label class="block min-w-0">
                    <span class="form-label">Status</span>
                    <select name="status" class="w-full min-w-0 max-w-full">
                        <option value="">Semua status</option>
                        @foreach(['valid', 'late', 'invalid_face', 'invalid_location', 'invalid_both'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Enums\AttendanceStatus::from($status)->label() }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="flex min-w-0 flex-col gap-2 sm:col-span-2 sm:flex-row sm:items-end lg:col-span-1">
                    <button type="submit" class="btn-primary w-full flex-1 sm:w-auto">Terapkan Filter</button>
                    @if($hasFilters)
                        <a href="{{ route('attendances.index') }}" class="btn-secondary w-full shrink-0 sm:w-auto" title="Reset filter">Reset</a>
                    @endif
                </div>
            </form>

            @perm('attendance.scan')
                <a href="{{ route('attendance.scan') }}" class="btn-primary flex w-full items-center justify-center lg:inline-flex lg:w-auto">
                    Scan Absensi
                </a>
            @endperm
        </div>

        @if($hasFilters)
            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                <span class="text-xs font-medium text-slate-500">Filter aktif:</span>
                @if(request('branch_id'))
                    @php $selectedBranch = $branches->firstWhere('id', (int) request('branch_id')); @endphp
                    <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-1 text-xs text-teal-800 ring-1 ring-teal-200">
                        {{ $selectedBranch?->name ?? 'Cabang' }}
                    </span>
                @endif
                @if(request('date'))
                    <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-1 text-xs text-teal-800 ring-1 ring-teal-200">
                        {{ \Carbon\Carbon::parse(request('date'))->format('d/m/Y') }}
                    </span>
                @endif
                @if(request('status'))
                    <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-1 text-xs text-teal-800 ring-1 ring-teal-200">
                        {{ \App\Enums\AttendanceStatus::from(request('status'))->label() }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 text-base text-slate-700">
        <p>
            Menampilkan
            <span class="font-bold text-slate-900">{{ $attendances->firstItem() ?? 0 }}–{{ $attendances->lastItem() ?? 0 }}</span>
            dari
            <span class="font-bold text-slate-900">{{ number_format($attendances->total(), 0, ',', '.') }}</span>
            rekap harian
        </p>
    </div>

    {{-- Mobile: kartu per pegawai/hari --}}
    <div class="space-y-4 lg:hidden">
        @forelse($attendances as $dayGroup)
            @include('partials.attendance-day-card', ['dayGroup' => $dayGroup])
        @empty
            <div class="panel p-8 text-center">
                <p class="text-lg font-bold text-slate-800">Belum ada data absensi</p>
                <p class="mt-2 text-base text-slate-600">
                    @if($hasFilters)
                        Tidak ditemukan absensi dengan filter yang dipilih.
                    @else
                        Data absensi akan muncul setelah pegawai melakukan scan.
                    @endif
                </p>
                @if($hasFilters)
                    <a href="{{ route('attendances.index') }}" class="link-action mt-4 inline-block">Reset filter</a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Desktop: tabel --}}
    <div class="panel-table table-mobile-scroll attendance-table-shell hidden lg:block">
        <table class="table-readable table-readable--scroll-only attendance-history-table">
            <thead>
                <tr>
                    <th class="cell-date">{{ __('app.date') }}</th>
                    <th class="cell-employee">{{ __('app.employee') }}</th>
                    <th class="cell-absensi-header">{{ __('attendance.attendance_time') }}</th>
                    <th class="cell-verify-header">{{ __('attendance.verification') }}</th>
                    <th class="cell-status-header cell-sticky-status">{{ __('app.status') }}</th>
                    <th class="cell-deduction-header cell-sticky-deduction">{{ __('attendance.deduction') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $dayGroup)
                    <tr>
                        <td class="cell-date">
                            <p class="cell-primary">{{ $dayGroup->date->format('d/m/Y') }}</p>
                            <p class="cell-secondary">{{ $dayGroup->date->locale(app()->getLocale())->translatedFormat('l') }}</p>
                        </td>
                        <td class="cell-employee">
                            <p class="cell-primary">{{ $dayGroup->employee->name }}</p>
                            <p class="cell-secondary">{{ $dayGroup->branchLabel() }}</p>
                        </td>
                        <td class="cell-absensi">
                            <div class="attendance-time-list">
                                @foreach($dayGroup->displayRecords() as $record)
                                    @include('partials.attendance-time-entry', ['attendance' => $record])
                                @endforeach
                            </div>
                        </td>
                        <td class="cell-verify">
                            <div class="attendance-verify-list">
                                @foreach($dayGroup->displayRecords() as $record)
                                    <div class="attendance-verify-item">
                                        @include('partials.attendance-day-verification', ['attendance' => $record, 'large' => true])
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="cell-status cell-sticky-status">
                            <div class="attendance-status-list">
                                @foreach($dayGroup->displayRecords() as $record)
                                    @include('partials.attendance-status-entry', ['attendance' => $record])
                                @endforeach
                            </div>
                        </td>
                        <td class="cell-deduction cell-sticky-deduction">
                            <div class="cell-deduction-inner">
                                @if($dayGroup->totalDeduction() > 0)
                                    <span class="deduction-amount">
                                        Rp {{ number_format($dayGroup->totalDeduction(), 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="empty-dash">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="mx-auto max-w-sm">
                                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-100">
                                        <svg class="h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                    </div>
                                    <p class="text-lg font-bold text-slate-800">Belum ada data absensi</p>
                                    <p class="mt-2 text-base text-slate-600">
                                        @if($hasFilters)
                                            Tidak ditemukan absensi dengan filter yang dipilih. Coba ubah filter atau reset.
                                        @else
                                            Data absensi akan muncul setelah pegawai melakukan scan atau absensi tercatat.
                                        @endif
                                    </p>
                                    @if($hasFilters)
                                        <a href="{{ route('attendances.index') }}" class="mt-4 inline-block text-base font-semibold text-teal-800 hover:underline">Reset filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>

    @if($attendances->hasPages())
        <div class="mt-4">{{ $attendances->links() }}</div>
    @endif

    @include('partials.attendance-photo-modal')
    </div>
@endsection
