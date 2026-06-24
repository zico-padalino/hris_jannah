@extends('layouts.app')

@section('title', 'Mesin Fingerprint')
@section('subtitle', 'ZKTeco — integrasi TCP port 4370')

@section('content')
    <div class="mb-6 rounded-xl border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900">
        <strong>Setup mesin TCP:</strong>
        <ol class="mt-2 list-decimal space-y-1 pl-5 text-xs">
            <li>Tambahkan mesin manual atau buat entri di bawah, lalu klik <strong>Kelola</strong></li>
            <li>Isi <strong>IP Mesin</strong> (contoh: 192.168.1.250) dan pilih <strong>Cabang</strong></li>
            <li>Pastikan mesin & server dalam jaringan yang sama (port <strong>4370</strong> terbuka)</li>
            <li>Daftarkan sidik jari & PIN pegawai langsung di mesin (PIN = field di data pegawai)</li>
        </ol>
        <p class="mt-3 text-xs text-teal-800">
            Jalankan <strong>2 terminal</strong>:
            <code class="mx-1">php artisan serve --host=0.0.0.0 --port=8000</code>
            dan
            <code class="mx-1">php artisan fingerprint:watch</code>
            (tarik log setiap {{ config('attendance.fingerprint_auto_pull_seconds') }} detik).
            Alternatif: <code>php artisan schedule:work</code>.
        </p>
        <p class="mt-1 text-xs text-slate-600">Mode: <strong>{{ $fingerprintLogMode }}</strong> — log absensi hanya via TCP, bukan ADMS push.</p>
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Nama</th>
                    <th>Cabang</th>
                    <th>IP Mesin</th>
                    <th>Status</th>
                    <th>Terakhir Tarik</th>
                    <th>Log</th>
                    <th class="cell-actions-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                    <tr>
                        <td class="font-mono text-xs">{{ $device->serial_number }}</td>
                        <td class="font-bold">{{ $device->name }}</td>
                        <td>{{ $device->branch->name ?? 'Belum diatur' }}</td>
                        <td class="font-mono text-xs">
                            @if($device->ip_address)
                                {{ $device->ip_address }}
                            @else
                                <span class="text-amber-700">Belum diisi</span>
                            @endif
                        </td>
                        <td>
                            @if($device->ip_address && $device->isOnline())
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-800">Terhubung</span>
                            @elseif($device->ip_address)
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-600">Menunggu tarik</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs text-amber-800">IP kosong</span>
                            @endif
                        </td>
                        <td>{{ $device->last_seen_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>{{ $device->logs_count }}</td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'fingerprint_devices',
                                'edit' => route('fingerprint-devices.edit', $device),
                                'editLabel' => 'Kelola',
                            ])
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                            Belum ada mesin. Klik Kelola pada entri mesin atau tambahkan dari halaman edit setelah mesin terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $devices->links() }}</div>
@endsection

@include('fingerprint-devices.partials.auto-refresh', [
    'pollUrl' => route('fingerprint-devices.index-log-sync-status'),
    'logSyncSnapshot' => $logSyncSnapshot,
])
