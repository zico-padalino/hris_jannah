@extends('layouts.app')

@section('title', __('pages.users.title'))

@section('content')
    <div class="users-page">
        <div class="users-page__toolbar">
            @moduleAction('users', 'create')
                <button type="button" class="btn-primary w-full sm:w-auto" data-user-create-open>+ {{ __('pages.users.add') }}</button>
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
                                <div class="table-actions" role="group" aria-label="{{ __('pages.users.col_actions') }}">
                                    @moduleAction('users', 'edit')
                                        <button
                                            type="button"
                                            class="table-action-btn table-action-btn--edit"
                                            title="{{ __('pages.users.edit') }}"
                                            aria-label="{{ __('pages.users.edit') }}"
                                            data-user-edit-open
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-user-email="{{ $user->email }}"
                                            data-user-role="{{ $user->role->value }}"
                                            data-user-branch-id="{{ $user->branch_id ?? '' }}"
                                            data-user-active="{{ $user->is_active ? '1' : '0' }}"
                                            data-user-update-url="{{ route('users.update', $user) }}"
                                        >
                                            <svg class="table-action-btn__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                            <span class="sr-only">{{ __('pages.users.edit') }}</span>
                                        </button>
                                    @endmoduleAction
                                </div>
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

    @moduleAction('users', 'create')
        @push('modals')
            @include('users._create-modal')
        @endpush
    @endmoduleAction

    @moduleAction('users', 'edit')
        @push('modals')
            @include('users._edit-modal')
        @endpush
    @endmoduleAction
@endsection
