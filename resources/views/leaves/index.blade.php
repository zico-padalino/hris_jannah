@extends('layouts.app')
@section('title', __('pages.leave.title'))
@section('subtitle', __('pages.leave.subtitle'))

@section('content')
@perm('leave.request')
    <div class="mb-4">
        <a href="{{ route('leaves.create') }}" class="btn-primary">{{ __('nav.leave_create') }}</a>
    </div>
@endperm

<div class="panel-table table-mobile-scroll">
    <table class="table-readable min-w-full">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                    <th class="px-4 py-3">{{ __('app.type') }}</th>
                    <th class="px-4 py-3">{{ __('app.period') }}</th>
                    <th class="px-4 py-3">{{ __('app.reason') }}</th>
                    <th class="px-4 py-3">{{ __('app.proof') }}</th>
                    <th class="px-4 py-3">{{ __('app.status') }}</th>
                    <th class="px-4 py-3">{{ __('app.admin_notes') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $leave)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $leave->type->label() }}</td>
                    <td class="px-4 py-3">{{ $leave->start_date->format('d/m/Y') }} – {{ $leave->end_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 max-w-xs">{{ $leave->reason }}</td>
                    <td class="px-4 py-3">@include('partials.leave-proof-link', ['leave' => $leave])</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-1 text-xs {{ $leave->status->badgeClass() }}">{{ $leave->status->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $leave->admin_notes ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center font-semibold text-slate-600">{{ __('leave.empty') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $leaves->links() }}</div>
@endsection
