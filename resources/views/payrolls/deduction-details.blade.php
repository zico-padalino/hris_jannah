@extends('layouts.app')

@section('title', 'Detail Potongan')
@section('subtitle', $item->employee->name.' · '.$payroll->name)

@section('content')
    @php
        $totalEvents = $lateCount + $invalidCount;
    @endphp

    <div class="payroll-deduction-page">
        <a href="{{ $backUrl }}" class="payroll-deduction-back">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </span>
            <span>Kembali</span>
        </a>

        <div class="payroll-deduction-hero panel">
            <div class="payroll-deduction-hero__top">
                <div class="min-w-0">
                    <p class="payroll-deduction-hero__label">Pegawai</p>
                    <p class="payroll-deduction-hero__name">{{ $item->employee->name }}</p>
                    <p class="payroll-deduction-hero__meta">{{ $item->employee->employee_number }} · {{ $payroll->name }}</p>
                </div>
                <div class="payroll-deduction-hero__net shrink-0 text-right">
                    <p class="payroll-deduction-hero__label">Gaji net</p>
                    <p class="payroll-deduction-hero__net-value">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="payroll-deduction-stats">
                <div class="payroll-deduction-stat payroll-deduction-stat--late">
                    <span class="payroll-deduction-stat__label">Terlambat</span>
                    <span class="payroll-deduction-stat__value">{{ $lateCount }}×</span>
                    <span class="payroll-deduction-stat__hint">@ Rp {{ number_format($deductionPer, 0, ',', '.') }}</span>
                </div>
                <div class="payroll-deduction-stat payroll-deduction-stat--invalid">
                    <span class="payroll-deduction-stat__label">Invalid</span>
                    <span class="payroll-deduction-stat__value">{{ $invalidCount }}×</span>
                    <span class="payroll-deduction-stat__hint">@ Rp {{ number_format($deductionPer, 0, ',', '.') }}</span>
                </div>
                <div class="payroll-deduction-stat payroll-deduction-stat--total">
                    <span class="payroll-deduction-stat__label">Total</span>
                    <span class="payroll-deduction-stat__value">Rp {{ number_format($item->deductions, 0, ',', '.') }}</span>
                    <span class="payroll-deduction-stat__hint">{{ $totalEvents }} kejadian</span>
                </div>
            </div>

            <p class="payroll-deduction-formula">
                {{ $totalEvents }} kejadian × Rp {{ number_format($deductionPer, 0, ',', '.') }}
                = <strong>Rp {{ number_format($item->deductions, 0, ',', '.') }}</strong>
            </p>
        </div>

        @if($item->employee->shift)
            <p class="payroll-deduction-shift">
                Jadwal <strong>{{ $item->employee->shift->name }}</strong>
                ({{ $item->employee->shift->formattedStartTime() }}–{{ $item->employee->shift->formattedEndTime() }},
                toleransi {{ $item->employee->shift->late_tolerance_minutes }} mnt)
            </p>
        @endif

        <div class="payroll-deduction-section-head">
            <h2 class="payroll-deduction-section-title">Rincian per absensi</h2>
            <p class="payroll-deduction-section-subtitle">{{ $attendances->count() }} baris potongan</p>
        </div>

        <div class="payroll-deduction-list space-y-2 lg:hidden">
            @forelse($attendances as $attendance)
                <article class="payroll-deduction-item panel">
                    <div class="payroll-deduction-item__head">
                        <div class="min-w-0">
                            <p class="payroll-deduction-item__date">{{ $attendance->attended_at->format('d/m/Y') }}</p>
                            <p class="payroll-deduction-item__meta">
                                {{ $attendance->attended_at->format('H:i') }} WIB
                                <span aria-hidden="true">·</span>
                                {{ $attendance->type->label() }}
                                <span aria-hidden="true">·</span>
                                {{ $attendance->source?->label() ?? '—' }}
                            </p>
                        </div>
                        <p class="payroll-deduction-item__amount shrink-0">
                            Rp {{ number_format($attendance->payrollDeductionAmount(), 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="payroll-deduction-item__foot">
                        @include('partials.attendance-status-badge', ['attendance' => $attendance])
                        @if($attendance->status === \App\Enums\AttendanceStatus::Late && $item->employee->shift)
                            <span class="payroll-deduction-item__schedule">Jadwal {{ $item->employee->shift->formattedStartTime() }}</span>
                        @endif
                        @if($attendance->is_late && $attendance->late_minutes)
                            <span class="payroll-deduction-item__late">+{{ $attendance->late_minutes }} mnt</span>
                        @endif
                    </div>
                </article>
            @empty
                <div class="panel p-6 text-center">
                    <p class="text-sm font-semibold text-slate-700">Tidak ada absensi terlambat atau invalid pada periode ini.</p>
                </div>
            @endforelse

            @if($attendances->isNotEmpty())
                <div class="payroll-deduction-total-row panel">
                    <span>Total potongan</span>
                    <strong>Rp {{ number_format($item->deductions, 0, ',', '.') }}</strong>
                </div>
            @endif
        </div>

        <div class="panel-table table-mobile-scroll hidden lg:block">
            <table class="table-readable table-readable--scroll-only min-w-full">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Jam Absen</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Jadwal Masuk</th>
                        <th class="px-4 py-3">Keterlambatan</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Sumber</th>
                        <th class="px-4 py-3">Potongan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3">{{ $attendance->attended_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $attendance->attended_at->format('H:i') }}</td>
                            <td class="px-4 py-3">{{ $attendance->type->label() }}</td>
                            <td class="px-4 py-3">
                                @if($attendance->status === \App\Enums\AttendanceStatus::Late && $item->employee->shift)
                                    {{ $item->employee->shift->formattedStartTime() }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($attendance->is_late && $attendance->late_minutes)
                                    <span class="font-medium text-orange-700">{{ $attendance->late_minutes }} mnt</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">@include('partials.attendance-status-badge', ['attendance' => $attendance])</td>
                            <td class="px-4 py-3">{{ $attendance->source?->label() ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-red-700">
                                Rp {{ number_format($attendance->payrollDeductionAmount(), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">Tidak ada absensi terlambat/invalid pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($attendances->isNotEmpty())
                    <tfoot class="border-t bg-slate-50 font-semibold">
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-right text-slate-600">Total</td>
                            <td class="px-4 py-3 text-red-700">Rp {{ number_format($item->deductions, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
