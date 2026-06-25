@extends('layouts.app')

@section('title', 'Payroll')

@section('content')
    @if(($pendingPayrollSignatureCount ?? 0) > 0)
        @include('partials.leave-alert-banner', [
            'count' => $pendingPayrollSignatureCount,
            'title' => __('pages.dashboard.signature_approval_title'),
            'message' => __('pages.dashboard.signature_approval_pending', ['count' => $pendingPayrollSignatureCount]),
            'href' => '#payroll-periods-table',
            'buttonLabel' => __('pages.dashboard.signature_approval_process'),
        ])
    @endif

    <form method="POST" action="{{ route('payrolls.store') }}" class="filter-bar mb-6 flex w-full flex-col gap-3 !mb-6 sm:flex-row sm:flex-wrap sm:items-end">
        @csrf
        <label class="min-w-0 sm:min-w-[12rem]">
            <span class="form-label">Cabang</span>
            <select name="branch_id" class="w-full">
                <option value="">Semua Cabang</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="min-w-0 sm:w-24">
            <span class="form-label">Bulan</span>
            <input name="month" type="number" min="1" max="12" value="{{ now()->month }}" class="w-full">
        </label>
        <label class="min-w-0 sm:w-28">
            <span class="form-label">Tahun</span>
            <input name="year" type="number" value="{{ now()->year }}" class="w-full">
        </label>
        <button type="submit" class="payroll-deduction-back w-full sm:w-auto">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            </span>
            <span>Buat Payroll</span>
        </button>
    </form>

    <div id="payroll-periods-table" class="panel-table table-mobile-scroll">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>Cabang</th>
                    <th>Status</th>
                    <th class="cell-actions-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                    <tr>
                        <td>{{ $period->name }}</td>
                        <td>{{ $period->branch->name ?? 'Semua' }}</td>
                        <td>{{ $period->status->label() }}</td>
                        <td class="cell-actions" data-label="Aksi">
                            <a href="{{ route('payrolls.show', $period) }}" class="payroll-deduction-back">
                                <span class="payroll-deduction-back__icon" aria-hidden="true">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </span>
                                <span>Detail</span>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $periods->links() }}</div>
@endsection
