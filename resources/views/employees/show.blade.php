@extends('layouts.app')

@section('title', $employee->name)
@section('subtitle', $employee->employee_number)

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('employees.edit', $employee) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Edit</a>
        <a href="{{ route('faces.enroll', $employee) }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Daftarkan Wajah</a>
        <a href="{{ route('employees.attendances', $employee) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Riwayat Absensi</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Data Pegawai</h2>
            <dl class="space-y-3 text-sm">
                <div><dt class="text-slate-500">Cabang</dt><dd>{{ $employee->branch->name }}</dd></div>
                <div><dt class="text-slate-500">Departemen</dt><dd>{{ $employee->department->name ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Jabatan</dt><dd>{{ $employee->position->name ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Shift Kerja</dt><dd>{{ $employee->shiftLabel() }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd>{{ ucfirst($employee->employment_status) }}</dd></div>
                <div><dt class="text-slate-500">Gaji Pokok</dt><dd>Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</dd></div>
                <div><dt class="text-slate-500">Email / Telepon</dt><dd>{{ $employee->email ?? '-' }} / {{ $employee->phone ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Alamat</dt><dd class="whitespace-pre-line">{{ $employee->address ?: '-' }}</dd></div>
                <div><dt class="text-slate-500">Tanggal Bergabung</dt><dd>{{ $employee->join_date?->format('d/m/Y') ?? '-' }}</dd></div>
                @if($employee->employment_status === 'contract' || $employee->contract_start_date || $employee->contract_end_date)
                    <div><dt class="text-slate-500">Periode Kontrak</dt>
                        <dd>
                            @if($employee->contract_start_date || $employee->contract_end_date)
                                {{ $employee->contract_start_date?->format('d/m/Y') ?? '—' }}
                                s/d
                                {{ $employee->contract_end_date?->format('d/m/Y') ?? '—' }}
                                @if($employee->contract_end_date)
                                    @php($daysLeft = now()->startOfDay()->diffInDays($employee->contract_end_date, false))
                                    <span @class([
                                        'ml-2 rounded-full px-2 py-0.5 text-xs font-semibold',
                                        'bg-red-100 text-red-800' => $daysLeft < 0,
                                        'bg-amber-100 text-amber-800' => $daysLeft >= 0 && $daysLeft <= 30,
                                        'bg-emerald-100 text-emerald-800' => $daysLeft > 30,
                                    ])>
                                        @if($daysLeft < 0)
                                            Berakhir {{ abs($daysLeft) }} hari lalu
                                        @elseif($daysLeft === 0)
                                            Berakhir hari ini
                                        @else
                                            {{ $daysLeft }} hari lagi
                                        @endif
                                    </span>
                                @endif
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                @endif
                <div><dt class="text-slate-500">PIN Fingerprint</dt><dd>{{ $employee->fingerprint_pin ?? 'Belum diatur' }}</dd></div>
            </dl>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Wajah Terdaftar ({{ $employee->faces->count() }})</h2>
            @forelse($employee->faces as $face)
                <div class="mb-3 flex items-center gap-3 rounded-lg border border-slate-100 p-3 text-sm">
                    @if($face->hasPhoto())
                        <img src="{{ $face->photo_url }}" alt="Wajah terdaftar" class="h-14 w-14 rounded-lg object-cover">
                    @endif
                    <div>
                        <p>Terdaftar: {{ $face->enrolled_at->format('d/m/Y H:i') }}</p>
                        <p>{{ $face->is_primary ? 'Wajah utama' : 'Wajah cadangan' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada wajah terdaftar. Daftarkan wajah sebelum absensi.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold">Absensi Terakhir</h2>
        <div class="overflow-x-auto">
            <table class="table-readable min-w-full">
                <thead class="border-b text-left text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Waktu</th>
                        <th class="py-2 pr-4">Tipe</th>
                        <th class="py-2 pr-4">Lokasi</th>
                        <th class="py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employee->attendances as $attendance)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 pr-4">{{ $attendance->attended_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 pr-4">{{ $attendance->type->label() }}</td>
                            <td class="py-3 pr-4">{{ $attendance->branchLocation->name ?? '-' }}</td>
                            <td class="py-3">@include('partials.attendance-status-badge', ['attendance' => $attendance])</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-500">Belum ada absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
