@extends('layouts.app')

@section('title', 'Kelola Mesin Fingerprint')
@section('subtitle', $fingerprintDevice->serial_number)

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('fingerprint-devices.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">← Kembali</a>
        @if($fingerprintDevice->branch_id)
            <form method="POST" action="{{ route('fingerprint-devices.sync-all', $fingerprintDevice) }}">
                @csrf
                <button class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Sinkron Jam Kerja + Pegawai</button>
            </form>
            <form method="POST" action="{{ route('fingerprint-devices.sync-shifts', $fingerprintDevice) }}">
                @csrf
                <button class="rounded-lg border border-teal-300 bg-teal-50 px-4 py-2 text-sm font-medium text-teal-800 hover:bg-teal-100">Sinkron Jam Kerja Saja</button>
            </form>
            <form method="POST" action="{{ route('fingerprint-devices.sync-employees', $fingerprintDevice) }}">
                @csrf
                <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Sinkron Pegawai Saja</button>
            </form>
            <form method="POST" action="{{ route('fingerprint-devices.pull-logs', $fingerprintDevice) }}">
                @csrf
                <button class="rounded-lg bg-amber-600 px-4 py-2 text-sm text-white hover:bg-amber-700">Tarik Log Absensi</button>
            </form>
            <p class="w-full text-xs text-slate-500">
                Mode <strong>TCP</strong> — log ditarik otomatis via port 4370. Jalankan
                <code>php artisan fingerprint:watch</code> atau <code>php artisan schedule:work</code>
                (setiap {{ config('attendance.fingerprint_auto_pull_seconds') }} detik).
                Daftarkan pegawai & sidik jari langsung di mesin; PIN harus sama dengan data pegawai.
            </p>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Pengaturan Mesin</h2>
            <form method="POST" action="{{ route('fingerprint-devices.update', $fingerprintDevice) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="mb-1 block text-sm font-medium">Serial Number</label>
                    <input value="{{ $fingerprintDevice->serial_number }}" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Nama Mesin</label>
                    <input name="name" value="{{ old('name', $fingerprintDevice->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">IP Mesin (TCP port 4370)</label>
                    <input name="ip_address" value="{{ old('ip_address', $fingerprintDevice->ip_address) }}" placeholder="192.168.1.250" required class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm">
                    <p class="mt-1 text-xs text-slate-500">Wajib diisi agar sistem bisa tarik log absensi via TCP.</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium">Cabang</label>
                    <select name="branch_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        <option value="">- Pilih Cabang -</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id', $fingerprintDevice->branch_id) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $fingerprintDevice->is_active)) class="rounded border-slate-300">
                    Mesin aktif
                </label>

                <button class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Simpan</button>
            </form>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Info Koneksi</h2>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-slate-500">Model</dt><dd>{{ $fingerprintDevice->model }}</dd></div>
                <div><dt class="text-slate-500">Terakhir Tarik Log</dt><dd>{{ $fingerprintDevice->last_seen_at?->format('d/m/Y H:i:s') ?? 'Belum pernah' }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd>{{ $fingerprintDevice->ip_address && $fingerprintDevice->isOnline() ? 'Terhubung' : ($fingerprintDevice->ip_address ? 'Menunggu tarik' : 'IP belum diisi') }}</dd></div>
            </dl>

            @if($fingerprintDevice->branch_id)
                <div class="mt-5 border-t border-slate-100 pt-4">
                    <h3 class="mb-2 text-sm font-semibold text-slate-800">Jadwal Jam Kerja Cabang</h3>
                    @if($branchShifts->isEmpty())
                        <p class="text-xs text-slate-500">Belum ada jadwal aktif. Buat di menu <strong>Jam Kerja</strong>.</p>
                    @else
                        <ul class="space-y-2 text-xs">
                            @foreach($branchShifts as $shift)
                                <li class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2">
                                    <span class="font-semibold text-slate-800">{{ $shift->name }}</span>
                                    <span class="text-slate-500"> · {{ $shift->formattedStartTime() }}–{{ $shift->formattedEndTime() }}</span>
                                    <span class="text-slate-500"> · {{ $shift->workDaysLabel(true) }}</span>
                                    <span class="text-slate-400"> · TZ#{{ min($shift->id, 50) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-lg font-semibold">Log Fingerprint Terakhir</h2>
        </div>
        <table class="table-readable min-w-full">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">PIN</th>
                    <th class="px-4 py-3">Pegawai</th>
                    <th class="px-4 py-3">Status Proses</th>
                    <th class="px-4 py-3">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ $log->punched_at->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 font-mono">{{ $log->device_pin }}</td>
                        <td class="px-4 py-3">{{ $log->employee->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($log->process_status === 'processed')
                                <span class="text-green-700">Berhasil</span>
                            @elseif($log->process_status === 'failed')
                                <span class="text-red-700">Gagal</span>
                            @else
                                <span class="text-slate-600">{{ $log->process_status }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $log->process_message ?? ($log->attendance_id ? 'Masuk ke absensi #' . $log->attendance_id : '-') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada log dari mesin ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@include('fingerprint-devices.partials.auto-refresh', [
    'pollUrl' => route('fingerprint-devices.log-sync-status', $fingerprintDevice),
    'logSyncSnapshot' => $logSyncSnapshot,
])
