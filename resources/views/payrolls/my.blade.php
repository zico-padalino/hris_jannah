@extends('layouts.app')

@section('title', 'Gaji Saya')

@section('content')
    <div class="panel-table table-mobile-scroll">
        <table class="table-readable min-w-full">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Gaji Pokok</th>
                    <th class="px-4 py-3">Potongan</th>
                    <th class="px-4 py-3">Net</th>
                    <th class="px-4 py-3">Catatan</th>
                    <th class="px-4 py-3 text-right">{{ __('pages.payroll_slip.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ $item->payrollPeriod->name }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($item->deductions, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 font-semibold">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600">{{ $item->notes ?? '-' }}</td>
                        <td class="px-4 py-3 text-right" data-label="{{ __('pages.payroll_slip.actions') }}">
                            @include('partials.payroll-item-actions', [
                                'payroll' => $item->payrollPeriod,
                                'item' => $item,
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
