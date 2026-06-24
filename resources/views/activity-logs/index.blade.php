@extends('layouts.app')

@section('title', __('pages.activity_logs.title'))
@section('subtitle', __('pages.activity_logs.subtitle'))
@section('back_url', route('dashboard'))

@section('content')
    <form method="GET" class="filter-bar mb-6">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <label class="block min-w-0 sm:col-span-2 lg:col-span-3">
                <span class="form-label">{{ __('pages.activity_logs.search') }}</span>
                <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('pages.activity_logs.search_placeholder') }}" class="w-full">
            </label>

            <label class="block min-w-0">
                <span class="form-label">{{ __('pages.activity_logs.action') }}</span>
                <select name="action" class="w-full">
                    <option value="">{{ __('pages.activity_logs.all_actions') }}</option>
                    @foreach($actions as $actionOption)
                        <option value="{{ $actionOption->value }}" @selected($action === $actionOption->value)>{{ $actionOption->label() }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block min-w-0">
                <span class="form-label">{{ __('pages.activity_logs.user') }}</span>
                <select name="user_id" class="w-full">
                    <option value="">{{ __('pages.activity_logs.all_users') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block min-w-0">
                <span class="form-label">{{ __('app.branch') }}</span>
                <select name="branch_id" class="w-full">
                    <option value="">{{ __('app.all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block min-w-0">
                <span class="form-label">{{ __('pages.activity_logs.date_from') }}</span>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full">
            </label>

            <label class="block min-w-0">
                <span class="form-label">{{ __('pages.activity_logs.date_to') }}</span>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full">
            </label>
        </div>

        <div class="filter-bar__actions">
            <button type="submit" class="btn-primary">{{ __('app.apply_filter') }}</button>
            <a href="{{ route('activity-logs.index') }}" class="btn-secondary">{{ __('app.reset') }}</a>
        </div>
    </form>

    <div class="panel-table overflow-x-auto">
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
                        <td class="whitespace-nowrap font-semibold">#{{ $log->id }}</td>
                        <td class="whitespace-nowrap">
                            <p class="font-semibold">{{ $log->created_at?->format('d/m/Y') }}</p>
                            <p class="text-sm app-muted-text">{{ $log->created_at?->format('H:i:s') }}</p>
                        </td>
                        <td>
                            <p class="font-semibold">{{ $log->user_name ?? __('pages.activity_logs.unknown_user') }}</p>
                            @if($log->user_email)
                                <p class="text-sm app-muted-text">{{ $log->user_email }}</p>
                            @endif
                        </td>
                        <td>{{ $log->user_role ? __('enums.user_role.'.$log->user_role) : '—' }}</td>
                        <td>{{ $log->branch?->name ?? '—' }}</td>
                        <td>
                            <span class="badge-readable {{ $log->action->badgeClass() }}">
                                {{ $log->action->label() }}
                            </span>
                        </td>
                        <td>{{ $log->module ?? '—' }}</td>
                        <td>{{ $log->subjectDisplay() }}</td>
                        <td class="min-w-[12rem]">{{ $log->description ?? '—' }}</td>
                        <td class="whitespace-nowrap text-sm app-muted-text">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-10 text-center font-semibold app-muted-text">
                            {{ __('pages.activity_logs.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif
@endsection
