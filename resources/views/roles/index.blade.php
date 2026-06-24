@extends('layouts.app')

@section('title', __('pages.roles.title'))
@section('subtitle', __('pages.roles.subtitle'))

@section('content')
    <div class="app-surface-section mb-4 px-4 py-3 text-sm">
        {{ __('pages.roles.system_roles_hint') }}
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th class="w-16">{{ __('pages.roles.col_no') }}</th>
                    <th>{{ __('pages.roles.col_group') }}</th>
                    <th>{{ __('pages.roles.col_description') }}</th>
                    <th class="w-48 cell-actions-header">{{ __('pages.roles.col_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $index => $role)
                    <tr>
                        <td class="font-semibold text-slate-600">{{ $index + 1 }}</td>
                        <td class="font-bold text-slate-900">{{ $role->label() }}</td>
                        <td class="max-w-xl text-slate-700">{{ $descriptions[$role->value] ?? $role->defaultDescription() }}</td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'roles',
                                'edit' => $role->isProtected() ? null : route('roles.edit', $role),
                                'extras' => [
                                    [
                                        'href' => route('roles.permissions', $role),
                                        'label' => __('pages.roles.access_rights'),
                                        'action_key' => 'access_rights',
                                        'variant' => 'accent',
                                        'icon' => 'shield',
                                    ],
                                ],
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
