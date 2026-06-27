@extends('layouts.app')

@section('title', 'Mesin Fingerprint')
@section('subtitle', 'ZKTeco — integrasi TCP port 4370')

@section('content')
<div class="fingerprint-device-page space-y-4">
    <div class="fingerprint-device-setup panel">
        <p class="fingerprint-device-setup__title"><strong>Setup mesin TCP</strong></p>
        <ol class="fingerprint-device-setup__list">
            <li>Tambahkan mesin manual atau buat entri di bawah, lalu klik <strong>Kelola</strong></li>
            <li>Isi <strong>IP Mesin</strong> (contoh: 192.168.1.250) dan pilih <strong>Cabang</strong></li>
            <li>Pastikan mesin & server dalam jaringan yang sama (port <strong>4370</strong> terbuka)</li>
            <li>Daftarkan sidik jari & PIN pegawai langsung di mesin (PIN = field di data pegawai)</li>
        </ol>
        <p class="fingerprint-device-setup__hint">
            Jalankan <strong>2 terminal</strong>:
            <code>php artisan serve --host=0.0.0.0 --port=8000</code>
            dan
            <code>php artisan fingerprint:watch</code>
            (tarik log setiap {{ config('attendance.fingerprint_auto_pull_seconds') }} detik).
            Alternatif: <code>php artisan schedule:work</code>.
        </p>
        <p class="fingerprint-device-setup__mode">Mode: <strong>{{ $fingerprintLogMode }}</strong> — log absensi hanya via TCP, bukan ADMS push.</p>
    </div>

    {{-- Mobile: kartu ringkas --}}
    <div class="fingerprint-device-list lg:hidden">
        @forelse($devices as $device)
            @include('fingerprint-devices._card', ['device' => $device])
        @empty
            <div class="fingerprint-device-empty panel">
                <p class="fingerprint-device-empty__text">Belum ada mesin. Klik Kelola pada entri mesin atau tambahkan dari halaman edit setelah mesin terdaftar.</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop: tabel --}}
    <div class="panel-table fingerprint-device-table hidden overflow-x-auto lg:block">
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
                        <td class="fingerprint-device-table__serial">{{ $device->serial_number }}</td>
                        <td class="cell-primary">{{ $device->name }}</td>
                        <td>{{ $device->branch->name ?? 'Belum diatur' }}</td>
                        <td class="fingerprint-device-table__serial">
                            @if($device->ip_address)
                                {{ $device->ip_address }}
                            @else
                                <span class="fingerprint-device-table__warn">Belum diisi</span>
                            @endif
                        </td>
                        <td>
                            @if($device->ip_address && $device->isOnline())
                                <span class="fingerprint-device-table__badge fingerprint-device-table__badge--online">Terhubung</span>
                            @elseif($device->ip_address)
                                <span class="fingerprint-device-table__badge fingerprint-device-table__badge--idle">Menunggu tarik</span>
                            @else
                                <span class="fingerprint-device-table__badge fingerprint-device-table__badge--empty">IP kosong</span>
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
                        <td colspan="8" class="fingerprint-device-table__empty">Belum ada mesin. Klik Kelola pada entri mesin atau tambahkan dari halaman edit setelah mesin terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($devices->hasPages())
        <div>{{ $devices->links() }}</div>
    @endif
</div>
@endsection

@include('fingerprint-devices.partials.auto-refresh', [
    'pollUrl' => route('fingerprint-devices.index-log-sync-status'),
    'logSyncSnapshot' => $logSyncSnapshot,
])
