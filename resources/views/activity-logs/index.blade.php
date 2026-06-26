@extends('layouts.app')

@section('title', __('pages.activity_logs.title'))
@section('subtitle', __('pages.activity_logs.subtitle'))
@section('back_url', route('dashboard'))

@section('content')
<div class="activity-log-page space-y-4">
    <form method="GET" class="filter-bar activity-log-filter">
        <div class="activity-log-filter__grid">
            <label class="activity-log-filter__field activity-log-filter__field--search">
                <span class="form-label">{{ __('pages.activity_logs.search') }}</span>
                <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('pages.activity_logs.search_placeholder') }}" class="w-full">
            </label>

            <label class="activity-log-filter__field">
                <span class="form-label">{{ __('pages.activity_logs.action') }}</span>
                <select name="action" class="w-full">
                    <option value="">{{ __('pages.activity_logs.all_actions') }}</option>
                    @foreach($actions as $actionOption)
                        <option value="{{ $actionOption->value }}" @selected($action === $actionOption->value)>{{ $actionOption->label() }}</option>
                    @endforeach
                </select>
            </label>

            <label class="activity-log-filter__field">
                <span class="form-label">{{ __('pages.activity_logs.user') }}</span>
                <select name="user_id" class="w-full">
                    <option value="">{{ __('pages.activity_logs.all_users') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="activity-log-filter__field">
                <span class="form-label">{{ __('app.branch') }}</span>
                <select name="branch_id" class="w-full">
                    <option value="">{{ __('app.all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="activity-log-filter__field">
                <span class="form-label">{{ __('pages.activity_logs.date_from') }}</span>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full">
            </label>

            <label class="activity-log-filter__field">
                <span class="form-label">{{ __('pages.activity_logs.date_to') }}</span>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full">
            </label>
        </div>

        <div class="filter-bar__actions">
            <button type="submit" class="btn-primary">{{ __('app.apply_filter') }}</button>
            <a href="{{ route('activity-logs.index') }}" class="btn-secondary">{{ __('app.reset') }}</a>
        </div>
    </form>

    {{-- Mobile & tablet: kartu ringkas --}}
    <div class="activity-log-list lg:hidden">
        @forelse($logs as $log)
            @include('activity-logs._item', ['log' => $log])
        @empty
            <div class="activity-log-empty panel">
                <p class="activity-log-empty__text">{{ __('pages.activity_logs.empty') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop: tabel padat --}}
    <div class="panel-table activity-log-table hidden overflow-x-auto lg:block">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>{{ __('pages.activity_logs.col_id') }}</th>
                    <th>{{ __('pages.activity_logs.col_time') }}</th>
                    <th>{{ __('pages.activity_logs.col_user') }}</th>
                    <th>{{ __('pages.activity_logs.col_role') }}</th>
                    <th>{{ __('app.branch') }}</th>
                    <th>{{ __('pages.activity_logs.col_action') }}</th>
                    <th>{{ __('pages.activity_logs.col_module') }}</th>
                    <th>{{ __('pages.activity_logs.col_subject') }}</th>
                    <th>{{ __('pages.activity_logs.col_description') }}</th>
                    <th>{{ __('pages.activity_logs.col_ip') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="activity-log-table__id">#{{ $log->id }}</td>
                        <td class="whitespace-nowrap">
                            <span class="activity-log-table__date">{{ $log->created_at?->format('d/m/Y') }}</span>
                            <span class="activity-log-table__meta">{{ $log->created_at?->format('H:i:s') }}</span>
                        </td>
                        <td>
                            <span class="activity-log-table__name">{{ $log->user_name ?? __('pages.activity_logs.unknown_user') }}</span>
                            @if($log->user_email)
                                <span class="activity-log-table__meta">{{ $log->user_email }}</span>
                            @endif
                        </td>
                        <td>{{ $log->user_role ? __('enums.user_role.'.$log->user_role) : '—' }}</td>
                        <td>{{ $log->branch?->name ?? '—' }}</td>
                        <td>
                            <span class="{{ $log->action->badgeClass() }}">{{ $log->action->label() }}</span>
                        </td>
                        <td class="activity-log-table__module">{{ $log->module ?? '—' }}</td>
                        <td>{{ $log->subjectDisplay() }}</td>
                        <td class="activity-log-table__desc">{{ $log->description ?? '—' }}</td>
                        <td class="activity-log-table__ip whitespace-nowrap">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="activity-log-table__empty">{{ __('pages.activity_logs.empty') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div>{{ $logs->links() }}</div>
    @endif
</div>
@endsection
