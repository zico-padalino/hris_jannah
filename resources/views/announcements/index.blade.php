@extends('layouts.app')

@section('title', __('pages.announcements.title'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
        <form method="GET" class="filter-bar flex w-full flex-col gap-3 !mb-0 sm:flex-row sm:flex-wrap sm:items-end">
            <label class="min-w-0 sm:min-w-[10rem]">
                <span class="form-label">{{ __('pages.announcements.branch') }}</span>
                <select name="branch_id" class="w-full">
                    <option value="">{{ __('pages.announcements.all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="min-w-0 sm:min-w-[10rem]">
                <span class="form-label">{{ __('pages.announcements.status_filter') }}</span>
                <select name="status" class="w-full">
                    <option value="">{{ __('pages.announcements.all_status') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('pages.announcements.status_active') }}</option>
                    <option value="scheduled" @selected(request('status') === 'scheduled')>{{ __('pages.announcements.status_scheduled') }}</option>
                    <option value="expired" @selected(request('status') === 'expired')>{{ __('pages.announcements.status_expired') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('pages.announcements.status_inactive') }}</option>
                </select>
            </label>
            <button type="submit" class="btn-primary w-full sm:w-auto">{{ __('pages.announcements.filter') }}</button>
        </form>
        @moduleAction('announcements', 'create')
            <a href="{{ route('announcements.create') }}" class="btn-primary w-full shrink-0 sm:w-auto">+ {{ __('pages.announcements.create') }}</a>
        @endmoduleAction
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>{{ __('pages.announcements.col_title') }}</th>
                    <th>{{ __('pages.announcements.col_period') }}</th>
                    <th>{{ __('pages.announcements.branch') }}</th>
                    <th>{{ __('pages.announcements.col_status') }}</th>
                    <th class="cell-actions-header">{{ __('pages.announcements.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($announcements as $announcement)
                    <tr>
                        <td>
                            <p class="font-bold">{{ $announcement->title }}</p>
                            <p class="mt-1 line-clamp-2 text-sm font-medium text-slate-600 dark:text-slate-400">{{ $announcement->content }}</p>
                        </td>
                        <td class="whitespace-nowrap font-semibold">
                            {{ $announcement->starts_at->format('d/m/Y') }}
                            <span class="text-slate-500">–</span>
                            {{ $announcement->ends_at->format('d/m/Y') }}
                        </td>
                        <td>{{ $announcement->branch->name ?? __('pages.announcements.all_branches') }}</td>
                        <td>
                            <span class="badge-readable {{ $announcement->statusBadgeClass() }}">
                                {{ $announcement->statusLabel() }}
                            </span>
                        </td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'announcements',
                                'edit' => route('announcements.edit', $announcement),
                                'delete' => route('announcements.destroy', $announcement),
                                'deleteConfirm' => __('pages.announcements.delete_confirm', ['title' => $announcement->title]),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center font-semibold text-slate-600">
                            {{ __('pages.announcements.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $announcements->links() }}</div>
@endsection
