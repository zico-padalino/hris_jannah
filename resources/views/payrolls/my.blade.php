@extends('layouts.app')

@section('title', __('pages.payroll.my_title'))
@section('subtitle', __('pages.payroll.my_subtitle'))

@section('content')
    <div class="panel-table table-mobile-scroll payroll-items-table-wrap">
        <table class="table-readable table-readable--scroll-only payroll-items-table min-w-full">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 cell-sticky cell-period">{{ __('pages.payroll.period') }}</th>
                    <th class="px-4 py-3 cell-money">{{ __('pages.payroll.base_salary') }}</th>
                    <th class="px-4 py-3 cell-money">{{ __('pages.payroll.deductions') }}</th>
                    <th class="px-4 py-3 cell-money">{{ __('pages.payroll.net_salary') }}</th>
                    <th class="px-4 py-3 cell-notes">{{ __('pages.payroll.notes') }}</th>
                    <th class="px-4 py-3 text-right cell-actions">{{ __('pages.payroll_slip.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3 cell-sticky cell-period">{{ $item->payrollPeriod->name }}</td>
                        <td class="px-4 py-3 cell-money">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 cell-money">Rp {{ number_format($item->deductions, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 cell-money cell-money--net">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 cell-notes text-xs text-slate-600" @if($item->notes) title="{{ $item->notes }}" @endif>{{ $item->notes ? \Illuminate\Support\Str::limit($item->notes, 36) : '-' }}</td>
                        <td class="px-4 py-3 text-right cell-actions">
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
