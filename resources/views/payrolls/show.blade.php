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
                    <span>{{ __('pages.payroll.regenerate') }}</span>
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
                    <span>{{ __('pages.payroll.finalize') }}</span>
                </button>
            </form>
        @endif
    </div>

    <div id="payroll-items-table" class="panel-table table-mobile-scroll payroll-items-table-wrap">
        <table class="table-readable table-readable--scroll-only payroll-items-table min-w-full">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 cell-sticky cell-employee">{{ __('pages.payroll.employee') }}</th>
                    <th class="px-4 py-3 cell-money">{{ __('pages.payroll.base_salary') }}</th>
                    <th class="px-4 py-3 cell-money">{{ __('pages.payroll.deductions') }}</th>
                    <th class="px-4 py-3 cell-money">{{ __('pages.payroll.net_salary') }}</th>
                    <th class="px-4 py-3 cell-notes">{{ __('pages.payroll.notes') }}</th>
                    <th class="px-4 py-3 text-center cell-actions">{{ __('pages.payroll_slip.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payroll->items as $item)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 cell-sticky cell-employee">{{ $item->employee->name }}</td>
                        <td class="px-4 py-3 cell-money">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 cell-money">Rp {{ number_format($item->deductions, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 cell-money cell-money--net">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 cell-notes text-xs text-slate-600" @if($item->notes) title="{{ $item->notes }}" @endif>{{ $item->notes ? \Illuminate\Support\Str::limit($item->notes, 36) : '-' }}</td>
                        <td class="px-4 py-3 text-center cell-actions">
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
