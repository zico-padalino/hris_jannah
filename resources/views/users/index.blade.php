@extends('layouts.app')

@section('title', __('pages.users.title'))

@section('content')
    <div class="users-page">
        <div class="users-page__toolbar">
            @moduleAction('users', 'create')
                <a href="{{ route('users.create') }}" class="btn-primary w-full sm:w-auto">+ {{ __('pages.users.add') }}</a>
            @endmoduleAction
        </div>

        <div class="panel-table table-mobile-scroll users-table-wrap">
            <table class="table-readable table-readable--scroll-only users-table min-w-full">
                <thead>
                    <tr>
                        <th class="cell-sticky cell-name">{{ __('pages.users.col_name') }}</th>
                        <th class="cell-email">{{ __('pages.users.col_email') }}</th>
                        <th class="cell-role">{{ __('pages.users.col_role') }}</th>
                        <th class="cell-branch">{{ __('pages.users.col_branch') }}</th>
                        <th class="cell-status">{{ __('pages.users.col_status') }}</th>
                        <th class="cell-actions-header cell-actions-col">{{ __('pages.users.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="cell-sticky cell-name font-bold">{{ $user->name }}</td>
                            <td class="cell-email">{{ $user->email }}</td>
                            <td class="cell-role">{{ $user->role->label() }}</td>
                            <td class="cell-branch">{{ $user->branch->name ?? __('pages.users.branch_hq') }}</td>
                            <td class="cell-status">
                                @include('partials.active-status-badge', ['active' => $user->is_active])
                            </td>
                            <td class="cell-actions cell-actions-col">
                                @include('partials.table-actions', [
                                    'module' => 'users',
                                    'edit' => route('users.edit', $user),
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="users-table__empty">{{ __('pages.users.empty') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="users-page__pagination">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
