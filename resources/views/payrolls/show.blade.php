@extends('layouts.app')

@section('title', $payroll->name)

@section('content')
    <div class="mb-4 flex flex-wrap gap-3">
        @if($payroll->status->value === 'draft')
            <form method="POST" action="{{ route('payrolls.regenerate', $payroll) }}">
                @csrf
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Generate Ulang</button>
            </form>
            <form method="POST" action="{{ route('payrolls.finalize', $payroll) }}">
                @csrf
                <button class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">Finalisasi</button>
            </form>
        @endif
    </div>

    <div class="panel-table table-mobile-scroll">
        <table class="table-readable min-w-full">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Pegawai</th>
                    <th class="px-4 py-3">Pokok</th>
                    <th class="px-4 py-3">Potongan</th>
                    <th class="px-4 py-3">Net</th>
                    <th class="px-4 py-3">Catatan</th>
                    <th class="px-4 py-3 text-right">{{ __('pages.payroll_slip.actions') }}</th>
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
                        <td class="px-4 py-3 text-right" data-label="{{ __('pages.payroll_slip.actions') }}">
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
