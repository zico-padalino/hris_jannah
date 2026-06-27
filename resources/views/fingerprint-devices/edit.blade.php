@extends('layouts.app')

@section('title', 'Kelola Mesin Fingerprint')
@section('subtitle', $fingerprintDevice->serial_number)

@section('content')
@php
    $isConnected = $fingerprintDevice->ip_address && $fingerprintDevice->isOnline();
    $statusLabel = match (true) {
        $isConnected => 'Terhubung',
        (bool) $fingerprintDevice->ip_address => 'Menunggu tarik',
        default => 'IP kosong',
    };
    $statusClass = match (true) {
        $isConnected => 'fingerprint-device-card__status--online',
        (bool) $fingerprintDevice->ip_address => 'fingerprint-device-card__status--idle',
        default => 'fingerprint-device-card__status--empty',
    };
@endphp

<div class="fingerprint-device-edit-page space-y-3">
    <div class="fingerprint-device-edit__top">
        <a href="{{ route('fingerprint-devices.index') }}" class="payroll-deduction-back fingerprint-device-edit__back">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </span>
            <span>Kembali</span>
        </a>

        <div class="fingerprint-device-edit__stats panel">
            <div class="fingerprint-device-edit__stat">
                <span class="fingerprint-device-edit__stat-label">Status</span>
                <span class="fingerprint-device-card__status {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <div class="fingerprint-device-edit__stat">
                <span class="fingerprint-device-edit__stat-label">Tarik log</span>
                <span class="fingerprint-device-edit__stat-value">{{ $fingerprintDevice->last_seen_at?->format('d/m H:i') ?? '—' }}</span>
            </div>
            <div class="fingerprint-device-edit__stat">
                <span class="fingerprint-device-edit__stat-label">Model</span>
                <span class="fingerprint-device-edit__stat-value">{{ $fingerprintDevice->model ?: '—' }}</span>
            </div>
            <div class="fingerprint-device-edit__stat">
                <span class="fingerprint-device-edit__stat-label">Total log</span>
                <span class="fingerprint-device-edit__stat-value">{{ number_format($fingerprintDevice->logs_count) }}</span>
            </div>
        </div>
    </div>

    @if($fingerprintDevice->branch_id)
        <div class="fingerprint-device-edit__toolbar panel">
            <div class="fingerprint-device-edit__sync">
                <form method="POST" action="{{ route('fingerprint-devices.sync-all', $fingerprintDevice) }}" class="fingerprint-device-edit__sync-form">
                    @csrf
                    <button type="submit" class="btn-primary fingerprint-device-edit__sync-btn">Sync Jadwal + Pegawai</button>
                </form>
                <form method="POST" action="{{ route('fingerprint-devices.sync-shifts', $fingerprintDevice) }}" class="fingerprint-device-edit__sync-form">
                    @csrf
                    <button type="submit" class="btn-secondary fingerprint-device-edit__sync-btn">Sync Jadwal</button>
                </form>
                <form method="POST" action="{{ route('fingerprint-devices.sync-employees', $fingerprintDevice) }}" class="fingerprint-device-edit__sync-form">
                    @csrf
                    <button type="submit" class="btn-secondary fingerprint-device-edit__sync-btn">Sync Pegawai</button>
                </form>
                <form method="POST" action="{{ route('fingerprint-devices.pull-logs', $fingerprintDevice) }}" class="fingerprint-device-edit__sync-form">
                    @csrf
                    <button type="submit" class="btn-primary fingerprint-device-edit__sync-btn">Tarik Log</button>
                </form>
            </div>
            <p class="fingerprint-device-edit__hint">
                TCP port 4370 · <code>php artisan fingerprint:watch</code> setiap {{ config('attendance.fingerprint_auto_pull_seconds') }} detik.
            </p>
        </div>
    @else
        <div class="fingerprint-device-edit__notice panel">
            <p class="fingerprint-device-edit__notice-text">Pilih cabang dan simpan untuk mengaktifkan sinkronisasi.</p>
        </div>
    @endif

    <div class="grid gap-3 lg:grid-cols-2">
        <div class="panel fingerprint-device-form">
            <h2 class="fingerprint-device-form__title">Pengaturan</h2>
            <form method="POST" action="{{ route('fingerprint-devices.update', $fingerprintDevice) }}" class="fingerprint-device-form__body">
                @csrf
                @method('PUT')

                <div class="fingerprint-device-form__grid">
                    <label class="fingerprint-device-form__field">
                        <span class="form-label">Serial</span>
                        <input value="{{ $fingerprintDevice->serial_number }}" readonly class="fingerprint-device-form__readonly w-full">
                    </label>
                    <label class="fingerprint-device-form__field">
                        <span class="form-label">Nama</span>
                        <input name="name" value="{{ old('name', $fingerprintDevice->name) }}" required class="w-full">
                    </label>
                </div>

                <div class="fingerprint-device-form__grid">
                    <label class="fingerprint-device-form__field">
                        <span class="form-label">IP (port 4370)</span>
                        <input name="ip_address" value="{{ old('ip_address', $fingerprintDevice->ip_address) }}" placeholder="192.168.1.250" required class="fingerprint-device-form__mono w-full">
                    </label>
                    <label class="fingerprint-device-form__field">
                        <span class="form-label">Cabang</span>
                        <select name="branch_id" required class="w-full">
                            <option value="">— Pilih —</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected(old('branch_id', $fingerprintDevice->branch_id) == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label class="fingerprint-device-form__toggle">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $fingerprintDevice->is_active))>
                    <span class="fingerprint-device-form__toggle-box" aria-hidden="true"></span>
                    <span class="fingerprint-device-form__toggle-title">Mesin aktif</span>
                </label>

                <button type="submit" class="btn-primary fingerprint-device-form__submit">Simpan</button>
            </form>
        </div>

        <div class="panel fingerprint-device-info">
            <h2 class="fingerprint-device-info__title">Info & Jadwal</h2>
            <dl class="fingerprint-device-info__list">
                <div class="fingerprint-device-info__row">
                    <dt>IP</dt>
                    <dd class="fingerprint-device-form__mono">{{ $fingerprintDevice->ip_address ?: '—' }}</dd>
                </div>
                <div class="fingerprint-device-info__row">
                    <dt>Cabang</dt>
                    <dd>{{ $fingerprintDevice->branch->name ?? '—' }}</dd>
                </div>
            </dl>

            @if($fingerprintDevice->branch_id)
                @if($branchShifts->isEmpty())
                    <p class="fingerprint-device-info__shifts-empty">Belum ada jadwal aktif di cabang ini.</p>
                @else
                    <ul class="fingerprint-device-shift-list">
                        @foreach($branchShifts as $shift)
                            <li class="fingerprint-device-shift-item">
                                <span class="fingerprint-device-shift-item__name">{{ $shift->name }}</span>
                                <span class="fingerprint-device-shift-item__meta">{{ $shift->formattedStartTime() }}–{{ $shift->formattedEndTime() }} · {{ $shift->workDaysLabel(true) }} · TZ#{{ min($shift->id, 50) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>
    </div>

    <div class="fingerprint-device-edit__logs-link panel">
        <div class="fingerprint-device-edit__logs-main">
            <h2 class="fingerprint-device-edit__logs-title">Log Fingerprint</h2>
            <p class="fingerprint-device-edit__logs-desc">{{ number_format($fingerprintDevice->logs_count) }} log tercatat · ditampilkan per halaman</p>
        </div>
        <a href="{{ route('fingerprint-devices.logs', $fingerprintDevice) }}" class="btn-secondary fingerprint-device-edit__logs-btn">Lihat Log</a>
    </div>
</div>
@endsection

@include('fingerprint-devices.partials.auto-refresh', [
    'pollUrl' => route('fingerprint-devices.log-sync-status', $fingerprintDevice),
    'logSyncSnapshot' => $logSyncSnapshot,
])
