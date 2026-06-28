@extends('layouts.app')

@section('title', __('pages.payroll.deduction_title'))
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
            <span>{{ __('pages.payroll_slip.back') }}</span>
        </a>

        <div class="payroll-deduction-hero panel">
            <div class="payroll-deduction-hero__top">
                <div class="min-w-0">
                    <p class="payroll-deduction-hero__label">{{ __('pages.payroll.employee') }}</p>
                    <p class="payroll-deduction-hero__name">{{ $item->employee->name }}</p>
                    <p class="payroll-deduction-hero__meta">{{ $item->employee->employee_number }} · {{ $payroll->name }}</p>
                </div>
                <div class="payroll-deduction-hero__net shrink-0 text-right">
                    <p class="payroll-deduction-hero__label">{{ __('pages.payroll.net_label') }}</p>
                    <p class="payroll-deduction-hero__net-value">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="payroll-deduction-stats">
                <div class="payroll-deduction-stat payroll-deduction-stat--late">
                    <span class="payroll-deduction-stat__label">{{ __('pages.payroll.late') }}</span>
                    <span class="payroll-deduction-stat__value">{{ $lateCount }}×</span>
                    <span class="payroll-deduction-stat__hint">@ Rp {{ number_format($deductionPer, 0, ',', '.') }}</span>
                </div>
                <div class="payroll-deduction-stat payroll-deduction-stat--invalid">
                    <span class="payroll-deduction-stat__label">{{ __('pages.payroll.invalid') }}</span>
                    <span class="payroll-deduction-stat__value">{{ $invalidCount }}×</span>
                    <span class="payroll-deduction-stat__hint">@ Rp {{ number_format($deductionPer, 0, ',', '.') }}</span>
                </div>
                <div class="payroll-deduction-stat payroll-deduction-stat--total">
                    <span class="payroll-deduction-stat__label">{{ __('pages.payroll.total') }}</span>
                    <span class="payroll-deduction-stat__value">Rp {{ number_format($item->deductions, 0, ',', '.') }}</span>
                    <span class="payroll-deduction-stat__hint">{{ __('pages.payroll.events', ['count' => $totalEvents]) }}</span>
                </div>
            </div>

            <p class="payroll-deduction-formula">
                {{ __('pages.payroll.formula', ['count' => $totalEvents, 'amount' => number_format($deductionPer, 0, ',', '.')]) }}
                <strong>Rp {{ number_format($item->deductions, 0, ',', '.') }}</strong>
            </p>
        </div>

        @if($item->employee->shift)
            <p class="payroll-deduction-shift">
                {{ __('pages.payroll.shift_schedule', [
                    'name' => $item->employee->shift->name,
                    'start' => $item->employee->shift->formattedStartTime(),
                    'end' => $item->employee->shift->formattedEndTime(),
                    'minutes' => $item->employee->shift->late_tolerance_minutes,
                ]) }}
            </p>
        @endif

        <div class="payroll-deduction-section-head">
            <h2 class="payroll-deduction-section-title">{{ __('pages.payroll.attendance_breakdown_title') }}</h2>
            <p class="payroll-deduction-section-subtitle">{{ __('pages.payroll.attendance_breakdown_count', ['count' => $attendances->count()]) }}</p>
        </div>

        <div class="panel-table table-mobile-scroll payroll-deduction-table-wrap">
            <table class="table-readable table-readable--scroll-only payroll-deduction-table min-w-full">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 cell-sticky cell-date">{{ __('pages.payroll.col_date') }}</th>
                        <th class="px-4 py-3 cell-time">{{ __('pages.payroll.col_time') }}</th>
                        <th class="px-4 py-3 cell-type">{{ __('pages.payroll.col_type') }}</th>
                        <th class="px-4 py-3 cell-schedule">{{ __('pages.payroll.col_schedule') }}</th>
                        <th class="px-4 py-3 cell-late">{{ __('pages.payroll.col_late') }}</th>
                        <th class="px-4 py-3 cell-status">{{ __('pages.payroll.col_status') }}</th>
                        <th class="px-4 py-3 cell-source">{{ __('pages.payroll.col_source') }}</th>
                        <th class="px-4 py-3 cell-deduction">{{ __('pages.payroll.col_deduction') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 cell-sticky cell-date">{{ $attendance->attended_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 cell-time font-medium">{{ $attendance->attended_at->format('H:i') }}</td>
                            <td class="px-4 py-3 cell-type">{{ $attendance->type->label() }}</td>
                            <td class="px-4 py-3 cell-schedule">
                                @if($attendance->status === \App\Enums\AttendanceStatus::Late && $item->employee->shift)
                                    {{ $item->employee->shift->formattedStartTime() }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 cell-late">
                                @if($attendance->is_late && $attendance->late_minutes)
                                    <span class="font-medium text-orange-700">{{ $attendance->late_minutes }} mnt</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 cell-status">@include('partials.attendance-status-badge', ['attendance' => $attendance])</td>
                            <td class="px-4 py-3 cell-source">{{ $attendance->source?->label() ?? '—' }}</td>
                            <td class="px-4 py-3 cell-deduction font-medium text-red-700">
                                Rp {{ number_format($attendance->payrollDeductionAmount(), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">{{ __('pages.payroll.no_deductible_attendance') }}</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($attendances->isNotEmpty())
                    <tfoot class="border-t bg-slate-50 font-semibold">
                        <tr>
                            <td colspan="7" class="px-4 py-3 text-right text-slate-600">{{ __('pages.payroll.total') }}</td>
                            <td class="px-4 py-3 cell-deduction text-red-700">Rp {{ number_format($item->deductions, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
