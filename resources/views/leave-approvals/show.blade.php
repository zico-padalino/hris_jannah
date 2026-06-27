@extends('layouts.app')

@php
    $categoryRoute = 'leave-approvals.'.$category;
    $categoryLabels = [
        'cuti' => __('leave.category_leave'),
        'izin' => __('leave.category_permission'),
        'lembur' => __('leave.category_overtime'),
    ];
@endphp

@section('title', __('pages.leave.'.$category.'.approval_title'))
@section('subtitle', __('pages.leave.'.$category.'.approval_subtitle'))

@section('content')
<div class="leave-approval-page space-y-3">
    <nav class="leave-approval-tabs panel" aria-label="{{ __('leave.approval_tabs') }}">
        @foreach(['cuti', 'izin', 'lembur'] as $tab)
            @php
                $tabPending = $tab === 'cuti' ? ($pendingBreakdown['cuti'] ?? 0) : ($tab === 'izin' ? ($pendingBreakdown['izin'] ?? 0) : ($pendingBreakdown['lembur'] ?? 0));
            @endphp
            <a
                href="{{ route('leave-approvals.'.$tab, ['status' => $status === 'all' ? 'pending' : $status]) }}"
                @class(['leave-approval-tabs__link', 'leave-approval-tabs__link--active' => $category === $tab])
            >
                {{ $categoryLabels[$tab] }}
                @if($tabPending > 0)
                    @include('partials.count-badge', [
                        'count' => $tabPending,
                        'variant' => 'sidebar-module',
                        'label' => __('app.new'),
                        'pulse' => $category === $tab,
                    ])
                @endif
            </a>
        @endforeach
    </nav>

    @if($pendingCount > 0 && $status === 'pending')
        @include('partials.leave-alert-banner', [
            'count' => $pendingCount,
            'title' => __('leave.approval_alert_title'),
            'message' => __('leave.approval_alert_category', ['count' => $pendingCount, 'category' => $categoryLabels[$category]]),
            'href' => route($categoryRoute, ['status' => 'pending']),
            'buttonLabel' => __('leave.review_list'),
        ])
    @endif

    <div class="grid gap-3 sm:grid-cols-3">
        <div @class(['dashboard-stat-card panel dashboard-stat-card--campfire p-3', 'leave-approval-stat--pulse' => $pendingCount > 0 && $status === 'pending'])>
            <p class="dashboard-stat-card__label text-sm font-semibold">{{ __('app.pending') }}</p>
            <p class="dashboard-stat-card__value mt-1 text-xl font-extrabold">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--emerald p-3">
            <p class="dashboard-stat-card__label text-sm font-semibold">{{ __('app.approved') }}</p>
            <p class="dashboard-stat-card__value mt-1 text-xl font-extrabold">{{ number_format($stats['approved']) }}</p>
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--red p-3">
            <p class="dashboard-stat-card__label text-sm font-semibold">{{ __('app.rejected') }}</p>
            <p class="dashboard-stat-card__value mt-1 text-xl font-extrabold">{{ number_format($stats['rejected']) }}</p>
        </div>
    </div>

    <div class="filter-bar leave-approval-filter">
        <span class="form-label leave-approval-filter__label">{{ __('leave.filter_status') }}</span>
        <div class="leave-approval-filter__status">
            @foreach(['pending' => __('app.pending'), 'approved' => __('app.approved'), 'rejected' => __('app.rejected'), 'all' => __('app.all')] as $value => $label)
                <a
                    href="{{ route($categoryRoute, ['status' => $value]) }}"
                    @class(['leave-approval-filter__btn', $status === $value ? 'btn-primary' : 'btn-secondary'])
                >
                    {{ $label }}
                    @if($value === 'pending' && $pendingCount > 0)
                        @include('partials.count-badge', ['count' => $pendingCount, 'variant' => 'default', 'pulse' => true])
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <div class="leave-approval-list lg:hidden">
        @forelse($leaves as $leave)
            @include('leave-approvals._item', ['leave' => $leave])
        @empty
            <div class="leave-approval-empty panel">
                <p class="leave-approval-empty__text">{{ __('leave.approval_empty_category', ['category' => $categoryLabels[$category]]) }}</p>
            </div>
        @endforelse
    </div>

    <div class="panel-table leave-approval-table hidden overflow-x-auto lg:block">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>{{ __('app.employee') }}</th>
                    <th>{{ __('app.branch') }}</th>
                    <th>{{ __('app.type') }}</th>
                    <th>{{ __('app.period') }}</th>
                    <th>{{ __('app.reason') }}</th>
                    <th>{{ __('app.proof') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th>{{ __('leave.processed_by') }}</th>
                    <th>{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaves as $leave)
                    <tr>
                        <td data-label="{{ __('app.employee') }}">
                            <span class="cell-primary">{{ $leave->employee->name }}</span>
                        </td>
                        <td data-label="{{ __('app.branch') }}">{{ $leave->branch->name }}</td>
                        <td data-label="{{ __('app.type') }}">{{ $leave->type->label() }}</td>
                        <td class="whitespace-nowrap" data-label="{{ __('app.period') }}">
                            {{ $leave->start_date->format('d/m/Y') }} – {{ $leave->end_date->format('d/m/Y') }}
                        </td>
                        <td class="leave-approval-table__reason" data-label="{{ __('app.reason') }}">
                            <span>{{ $leave->reason }}</span>
                            @if($leave->admin_notes)
                                <span class="leave-approval-table__note">{{ __('app.admin_notes') }}: {{ $leave->admin_notes }}</span>
                            @endif
                        </td>
                        <td data-label="{{ __('app.proof') }}">@include('partials.leave-proof-link', ['leave' => $leave])</td>
                        <td data-label="{{ __('app.status') }}">
                            <span class="leave-approval-status leave-approval-status--{{ $leave->status->value }}">{{ $leave->status->label() }}</span>
                        </td>
                        <td data-label="{{ __('leave.processed_by') }}">
                            @if($leave->approver)
                                <span class="cell-primary">{{ $leave->approver->name }}</span>
                                <span class="leave-approval-table__note">{{ $leave->approved_at?->format('d/m/Y H:i') }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td data-label="{{ __('app.actions') }}">
                            @if($leave->status->value === 'pending')
                                <div class="leave-approval-actions leave-approval-actions--table">
                                    <form method="POST" action="{{ route('leave-approvals.approve', $leave) }}" class="leave-approval-actions__form">
                                        @csrf
                                        <input type="text" name="admin_notes" placeholder="{{ __('leave.approval_note_optional') }}" class="leave-approval-actions__input w-full">
                                        <button type="submit" class="btn-primary leave-approval-actions__btn">{{ __('app.approve') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('leave-approvals.reject', $leave) }}" class="leave-approval-actions__form">
                                        @csrf
                                        <input type="text" name="admin_notes" placeholder="{{ __('leave.approval_reject_reason') }}" class="leave-approval-actions__input w-full">
                                        <button type="submit" class="btn-secondary leave-approval-actions__btn leave-approval-actions__btn--reject">{{ __('app.reject') }}</button>
                                    </form>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="leave-approval-table__empty">{{ __('leave.approval_empty_category', ['category' => $categoryLabels[$category]]) }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($leaves->hasPages())
        <div>{{ $leaves->links() }}</div>
    @endif
</div>
@endsection
