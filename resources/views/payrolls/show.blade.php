@extends('layouts.app')

@section('title', $payroll->name)

@section('content')
    @if(($pendingSignatureCount ?? 0) > 0)
        @include('partials.leave-alert-banner', [
            'count' => $pendingSignatureCount,
            'title' => __('pages.dashboard.signature_approval_title'),
            'message' => __('pages.dashboard.signature_approval_pending', ['count' => $pendingSignatureCount]),
            'href' => '#payroll-items-table',
            'buttonLabel' => __('pages.dashboard.signature_approval_process'),
        ])
    @endif

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('payrolls.index') }}" class="payroll-deduction-back">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </span>
            <span>{{ __('pages.payroll_slip.back') }}</span>
        </a>

        @if($payroll->status->value === 'draft')
            <form method="POST" action="{{ route('payrolls.regenerate', $payroll) }}" class="inline">
                @csrf
                <button type="submit" class="payroll-deduction-back">
                    <span class="payroll-deduction-back__icon" aria-hidden="true">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                        </svg>
                    </span>
                    <span>Generate Ulang</span>
                </button>
            </form>
            <form method="POST" action="{{ route('payrolls.finalize', $payroll) }}" class="inline">
                @csrf
                <button type="submit" class="payroll-deduction-back">
                    <span class="payroll-deduction-back__icon" aria-hidden="true">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span>Finalisasi</span>
                </button>
            </form>
        @endif
    </div>

    <div id="payroll-items-table" class="panel-table table-mobile-scroll">
        <table class="table-readable min-w-full">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Pegawai</th>
                    <th class="px-4 py-3">Pokok</th>
                    <th class="px-4 py-3">Potongan</th>
                    <th class="px-4 py-3">Net</th>
                    <th class="px-4 py-3">Catatan</th>
                    <th class="px-4 py-3 text-center">{{ __('pages.payroll_slip.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payroll->items as $item)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ $item->employee->name }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($item->deductions, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-semibold">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $item->notes ?? '-' }}</td>
                        <td class="px-4 py-3 text-center" data-label="{{ __('pages.payroll_slip.actions') }}">
                            @include('partials.payroll-item-actions', [
                                'payroll' => $payroll,
                                'item' => $item,
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
