@extends('layouts.app')
@section('title', __('pages.leave.approval_title'))
@section('subtitle', __('pages.leave.approval_subtitle'))

@section('content')
@if($pendingCount > 0 && $status === 'pending')
    @include('partials.leave-alert-banner', [
        'count' => $pendingCount,
        'title' => __('leave.approval_alert_title'),
        'message' => __('leave.approval_alert_message', ['count' => $pendingCount]),
        'href' => route('leave-approvals.index', ['status' => 'pending']),
        'buttonLabel' => __('leave.review_list'),
    ])
@endif

<div class="mb-6 grid gap-4 lg:grid-cols-3">
    <div @class([
        'panel p-4',
        'app-notification-panel--active leave-badge-pulse' => $pendingCount > 0,
    ])>
        <p class="text-sm font-bold text-slate-700">{{ __('pages.leave.approval_title') }}</p>
        <p @class(['mt-1 text-3xl font-extrabold', 'app-pending-value' => $pendingCount > 0, 'text-slate-400' => $pendingCount === 0])>
            @if($pendingCount > 0)
                <span class="app-notification-dot">
                    <span class="app-notification-dot__ping"></span>
                    <span class="app-notification-dot__core"></span>
                </span>
            @endif
            {{ $pendingCount }}
        </p>
        <p class="mt-1 text-sm font-semibold text-slate-600">{{ __('leave.waiting_approval') }}</p>
        <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold">
            <span class="rounded-md bg-emerald-100 px-2 py-1 text-emerald-800">{{ __('leave.category_leave') }} {{ $pendingBreakdown['cuti'] ?? 0 }}</span>
            <span class="rounded-md bg-sky-100 px-2 py-1 text-sky-800">{{ __('leave.category_permission') }} {{ $pendingBreakdown['izin'] ?? 0 }}</span>
            <span class="rounded-md bg-violet-100 px-2 py-1 text-violet-800">{{ __('leave.category_overtime') }} {{ $pendingBreakdown['lembur'] ?? 0 }}</span>
        </div>
    </div>
    <div class="panel p-4 lg:col-span-2">
        <p class="mb-2 text-sm font-bold text-slate-700">{{ __('leave.filter_status') }}</p>
        <div class="flex flex-wrap gap-2">
            @foreach(['pending' => __('app.pending'), 'approved' => __('app.approved'), 'rejected' => __('app.rejected'), 'all' => __('app.all')] as $value => $label)
                <a
                    href="{{ route('leave-approvals.index', ['status' => $value]) }}"
                    class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm font-semibold {{ $status === $value ? 'bg-teal-700 text-white' : 'border-2 border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}"
                >
                    {{ $label }}
                    @if($value === 'pending' && $pendingCount > 0)
                        @include('partials.count-badge', ['count' => $pendingCount, 'variant' => 'default', 'pulse' => true])
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="panel-table table-mobile-scroll">
    <table class="table-readable min-w-full">
        <thead class="bg-slate-50 text-left text-slate-500">
            <tr>
                <th class="px-4 py-3">Pegawai</th>
                <th class="px-4 py-3">Cabang</th>
                <th class="px-4 py-3">Tipe</th>
                <th class="px-4 py-3">Periode</th>
                <th class="px-4 py-3">Alasan</th>
                <th class="px-4 py-3">Bukti</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Diproses Oleh</th>
                <th class="px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $leave)
                <tr class="border-t align-top">
                    <td class="px-4 py-3 font-medium">{{ $leave->employee->name }}</td>
                    <td class="px-4 py-3">{{ $leave->branch->name }}</td>
                    <td class="px-4 py-3">{{ $leave->type->label() }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        {{ $leave->start_date->format('d/m/Y') }} – {{ $leave->end_date->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 max-w-xs">
                        <p>{{ $leave->reason }}</p>
                        @if($leave->admin_notes)
                            <p class="mt-1 text-xs text-slate-500">Catatan: {{ $leave->admin_notes }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">@include('partials.leave-proof-link', ['leave' => $leave])</td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-1 text-xs {{ $leave->status->badgeClass() }}">
                            {{ $leave->status->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($leave->approver)
                            <div>{{ $leave->approver->name }}</div>
                            <div class="text-xs text-slate-500">{{ $leave->approved_at?->format('d/m/Y H:i') }}</div>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($leave->status->value === 'pending')
                            <div class="space-y-2 min-w-[180px]">
                                <form method="POST" action="{{ route('leave-approvals.approve', $leave) }}" class="space-y-1">
                                    @csrf
                                    <input
                                        type="text"
                                        name="admin_notes"
                                        placeholder="Catatan (opsional)"
                                        class="w-full rounded border px-2 py-1 text-xs"
                                    >
                                    <button type="submit" class="w-full rounded bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                                        Setujui
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('leave-approvals.reject', $leave) }}" class="space-y-1">
                                    @csrf
                                    <input
                                        type="text"
                                        name="admin_notes"
                                        placeholder="Alasan penolakan"
                                        class="w-full rounded border px-2 py-1 text-xs"
                                    >
                                    <button type="submit" class="w-full rounded bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="px-4 py-10 text-center text-slate-500">
                        {{ __('leave.approval_empty') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $leaves->links() }}</div>
@endsection
