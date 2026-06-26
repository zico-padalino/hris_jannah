@extends('layouts.app')

@section('title', __('pages.reports.title'))
@section('subtitle', __('pages.reports.subtitle'))

@section('content')
@php
    $monthLabel = \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->locale(app()->getLocale())->translatedFormat('F Y');
    $totals = [
        'total' => $summary->sum('total'),
        'valid' => $summary->sum('valid_count'),
        'invalid' => $summary->sum('invalid_count'),
    ];
@endphp

<div class="report-page space-y-4">
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="dashboard-stat-card panel dashboard-stat-card--campfire p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">{{ __('pages.reports.total_attendance') }}</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ number_format($totals['total']) }}</p>
            <p class="report-stat-card__period">{{ $monthLabel }}</p>
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--emerald p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">{{ __('pages.reports.valid') }}</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ number_format($totals['valid']) }}</p>
            @if($totals['total'] > 0)
                <p class="report-stat-card__period">{{ round(($totals['valid'] / $totals['total']) * 100) }}%</p>
            @endif
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--red p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">{{ __('pages.reports.invalid') }}</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ number_format($totals['invalid']) }}</p>
            @if($totals['total'] > 0)
                <p class="report-stat-card__period">{{ round(($totals['invalid'] / $totals['total']) * 100) }}%</p>
            @endif
        </div>
    </div>

    <form method="GET" class="filter-bar report-filter">
        <label class="report-filter__field">
            <span class="form-label">{{ __('pages.reports.month') }}</span>
            <input type="month" name="month" value="{{ $month }}" class="w-full min-w-[10rem]">
        </label>
        <div class="filter-bar__actions">
            <button type="submit" class="btn-primary">{{ __('app.apply_filter') }}</button>
            @if(request()->filled('month') && request('month') !== now()->format('Y-m'))
                <a href="{{ route('reports.index') }}" class="btn-secondary">{{ __('app.reset') }}</a>
            @endif
        </div>
    </form>

    <div class="panel-table table-mobile-scroll report-table">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>{{ __('app.branch') }}</th>
                    <th>{{ __('pages.reports.total_attendance') }}</th>
                    <th>{{ __('pages.reports.valid') }}</th>
                    <th>{{ __('pages.reports.invalid') }}</th>
                    <th class="hidden md:table-cell">{{ __('pages.reports.valid_rate') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summary as $row)
                    @php
                        $validRate = $row->total > 0 ? round(($row->valid_count / $row->total) * 100) : 0;
                    @endphp
                    <tr>
                        <td data-label="{{ __('app.branch') }}">
                            <span class="cell-primary">{{ $row->branch->name ?? __('pages.reports.branch_fallback', ['id' => $row->branch_id]) }}</span>
                        </td>
                        <td data-label="{{ __('pages.reports.total_attendance') }}">{{ number_format($row->total) }}</td>
                        <td data-label="{{ __('pages.reports.valid') }}">
                            <span class="report-table__valid">{{ number_format($row->valid_count) }}</span>
                        </td>
                        <td data-label="{{ __('pages.reports.invalid') }}">
                            <span class="report-table__invalid">{{ number_format($row->invalid_count) }}</span>
                        </td>
                        <td class="hidden md:table-cell" data-label="{{ __('pages.reports.valid_rate') }}">
                            <span class="report-table__rate report-table__rate--{{ $validRate >= 90 ? 'good' : ($validRate >= 70 ? 'mid' : 'low') }}">{{ $validRate }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="report-table__empty">{{ __('pages.reports.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
