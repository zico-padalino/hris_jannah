@extends('layouts.app')

@section('title', __('pages.attendance_history.title'))
@section('subtitle', __('pages.attendance_history.subtitle'))

@section('content')
    <div class="attendance-history-page min-w-0">
    @php
        $hasFilters = request()->filled('branch_id') || request()->filled('date') || request()->filled('status');
    @endphp

    <div class="filter-bar attendance-history-filters min-w-0">
        <div class="flex min-w-0 flex-col gap-3 lg:flex-row lg:items-end lg:justify-between lg:gap-4">
            <form method="GET" class="grid min-w-0 flex-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:gap-4">
                @if(auth()->user()->role->value !== 'employee')
                    <label class="block min-w-0">
                        <span class="form-label">{{ __('app.branch') }}</span>
                        <select name="branch_id" class="w-full min-w-0 max-w-full">
                            <option value="">{{ __('app.all_branches') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif

                <label class="block min-w-0">
                    <span class="form-label">{{ __('app.date') }}</span>
                    <div class="form-date-wrap">
                        <input type="date" name="date" value="{{ request('date') }}" lang="id" class="form-date-input w-full min-w-0 max-w-full">
                    </div>
                </label>

                <label class="block min-w-0">
                    <span class="form-label">{{ __('app.status') }}</span>
                    <select name="status" class="w-full min-w-0 max-w-full">
                        <option value="">{{ __('app.all_status') }}</option>
                        @foreach(['valid', 'late', 'invalid_face', 'invalid_location', 'invalid_both'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ \App\Enums\AttendanceStatus::from($status)->label() }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="flex min-w-0 flex-col gap-2 sm:col-span-2 sm:flex-row sm:items-end lg:col-span-1">
                    <button type="submit" class="btn-primary w-full flex-1 sm:w-auto">{{ __('attendance.apply_filter') }}</button>
                    @if($hasFilters)
                        <a href="{{ route('attendances.index') }}" class="btn-secondary w-full shrink-0 sm:w-auto" title="{{ __('attendance.reset_filter') }}">{{ __('attendance.reset_filter') }}</a>
                    @endif
                </div>
            </form>

            @perm('attendance.scan')
                <a href="{{ route('attendance.scan') }}" class="btn-primary flex w-full items-center justify-center lg:inline-flex lg:w-auto">
                    {{ __('attendance.scan') }}
                </a>
            @endperm
        </div>

        @if($hasFilters)
            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 lg:mt-4 lg:pt-4">
                <span class="text-xs font-medium text-slate-500">{{ __('attendance.active_filters') }}</span>
                @if(request('branch_id'))
                    @php $selectedBranch = $branches->firstWhere('id', (int) request('branch_id')); @endphp
                    <span class="inline-flex items-center rounded-full bg-campfire-4 px-2.5 py-1 text-xs text-campfire-1 ring-1 ring-campfire-3">
                        {{ $selectedBranch?->name ?? __('app.branch') }}
                    </span>
                @endif
                @if(request('date'))
                    <span class="inline-flex items-center rounded-full bg-campfire-4 px-2.5 py-1 text-xs text-campfire-1 ring-1 ring-campfire-3">
                        {{ \Carbon\Carbon::parse(request('date'))->format('d/m/Y') }}
                    </span>
                @endif
                @if(request('status'))
                    <span class="inline-flex items-center rounded-full bg-campfire-4 px-2.5 py-1 text-xs text-campfire-1 ring-1 ring-campfire-3">
                        {{ \App\Enums\AttendanceStatus::from(request('status'))->label() }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <p class="attendance-history-summary mb-3 text-sm text-slate-700 lg:mb-4 lg:text-base">
        {!! __('attendance.showing_summary', [
            'from' => $attendances->firstItem() ?? 0,
            'to' => $attendances->lastItem() ?? 0,
            'total' => number_format($attendances->total(), 0, ',', '.'),
        ]) !!}
    </p>

    <div class="panel-table table-mobile-scroll attendance-table-shell">
        <table class="table-readable table-readable--scroll-only attendance-history-table">
            <thead>
                <tr>
                    <th class="cell-sticky cell-date">{{ __('app.date') }}</th>
                    <th class="cell-sticky cell-employee">{{ __('app.employee') }}</th>
                    <th class="cell-absensi-header">{{ __('attendance.attendance_time') }}</th>
                    <th class="cell-verify-header">{{ __('attendance.verification') }}</th>
                    <th class="cell-status-header">{{ __('app.status') }}</th>
                    <th class="cell-deduction-header">{{ __('attendance.deduction') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $dayGroup)
                    <tr>
                        <td class="cell-sticky cell-date">
                            <p class="cell-primary">{{ $dayGroup->date->format('d/m/Y') }}</p>
                            <p class="cell-secondary">{{ $dayGroup->date->locale(app()->getLocale())->translatedFormat('l') }}</p>
                        </td>
                        <td class="cell-sticky cell-employee">
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
                                        @include('partials.attendance-day-verification', ['attendance' => $record, 'large' => false])
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="cell-status">
                            <div class="attendance-status-list">
                                @foreach($dayGroup->displayRecords() as $record)
                                    @include('partials.attendance-status-entry', ['attendance' => $record])
                                @endforeach
                            </div>
                        </td>
                        <td class="cell-deduction">
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
                        <td colspan="6" class="px-4 py-10 text-center lg:py-16">
                            <div class="mx-auto max-w-sm">
                                <p class="text-base font-bold text-slate-800 lg:text-lg">{{ __('attendance.empty_title') }}</p>
                                <p class="mt-2 text-sm text-slate-600 lg:text-base">
                                    @if($hasFilters)
                                        {{ __('attendance.empty_filtered') }}
                                    @else
                                        {{ __('attendance.empty_default') }}
                                    @endif
                                </p>
                                @if($hasFilters)
                                    <a href="{{ route('attendances.index') }}" class="link-action mt-3 inline-block text-sm lg:mt-4 lg:text-base">{{ __('attendance.reset_filter') }}</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attendances->hasPages())
        <div class="mt-3 lg:mt-4">{{ $attendances->links() }}</div>
    @endif
    </div>
@endsection
