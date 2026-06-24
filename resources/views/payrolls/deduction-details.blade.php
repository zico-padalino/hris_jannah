@extends('layouts.app')

@section('title', 'Detail Potongan — '.$item->employee->name)
@section('subtitle', $payroll->name)

@section('content')
    <div class="mb-6">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('payrolls.show', $payroll) }}"
           class="text-sm text-teal-700 hover:underline">&larr; Kembali</a>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Pegawai</p>
            <p class="mt-1 font-semibold">{{ $item->employee->name }}</p>
            <p class="text-xs text-slate-500">{{ $item->employee->employee_number }}</p>
        </div>
        <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 shadow-sm">
            <p class="text-xs text-orange-700">Terlambat</p>
            <p class="mt-1 text-2xl font-bold text-orange-800">{{ $lateCount }}x</p>
            <p class="text-xs text-orange-700">@ Rp {{ number_format($deductionPer, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <p class="text-xs text-red-700">Invalid</p>
            <p class="mt-1 text-2xl font-bold text-red-800">{{ $invalidCount }}x</p>
            <p class="text-xs text-red-700">@ Rp {{ number_format($deductionPer, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Total Potongan</p>
            <p class="mt-1 text-2xl font-bold text-red-700">Rp {{ number_format($item->deductions, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500">Gaji net: Rp {{ number_format($item->net_salary, 0, ',', '.') }}</p>
        </div>
    </div>

    @if($item->employee->shift)
        <div class="mb-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
            Jadwal: <strong>{{ $item->employee->shift->name }}</strong>
            ({{ $item->employee->shift->formattedStartTime() }}–{{ $item->employee->shift->formattedEndTime() }},
            toleransi {{ $item->employee->shift->late_tolerance_minutes }} mnt)
        </div>
    @endif

    <div class="panel-table table-mobile-scroll">
        <table class="table-readable min-w-full">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Jam Absen</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Jadwal Masuk</th>
                    <th class="px-4 py-3">Keterlambatan</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Sumber</th>
                    <th class="px-4 py-3">Potongan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ $attendance->attended_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $attendance->attended_at->format('H:i') }}</td>
                        <td class="px-4 py-3">{{ $attendance->type->label() }}</td>
                        <td class="px-4 py-3">
                            @if($attendance->status === \App\Enums\AttendanceStatus::Late && $item->employee->shift)
                                {{ $item->employee->shift->formattedStartTime() }}
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($attendance->is_late && $attendance->late_minutes)
                                <span class="font-medium text-orange-700">{{ $attendance->late_minutes }} mnt</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">@include('partials.attendance-status-badge', ['attendance' => $attendance])</td>
                        <td class="px-4 py-3">{{ $attendance->source?->label() ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium text-red-700">
                            Rp {{ number_format($attendance->payrollDeductionAmount(), 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">Tidak ada absensi terlambat/invalid pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($attendances->isNotEmpty())
                <tfoot class="border-t bg-slate-50 font-semibold">
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-right text-slate-600">Total</td>
                        <td class="px-4 py-3 text-red-700">Rp {{ number_format($item->deductions, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
