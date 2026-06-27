@php
    $isConnected = $device->ip_address && $device->isOnline();
    $statusClass = match (true) {
        $isConnected => 'fingerprint-device-card__status--online',
        (bool) $device->ip_address => 'fingerprint-device-card__status--idle',
        default => 'fingerprint-device-card__status--empty',
    };
    $statusLabel = match (true) {
        $isConnected => 'Terhubung',
        (bool) $device->ip_address => 'Menunggu tarik',
        default => 'IP kosong',
    };
@endphp

<article class="fingerprint-device-card panel">
    <div class="fingerprint-device-card__head">
        <div class="fingerprint-device-card__main min-w-0">
            <h3 class="fingerprint-device-card__name">{{ $device->name }}</h3>
            <p class="fingerprint-device-card__sub">
                <span class="fingerprint-device-card__serial">{{ $device->serial_number }}</span>
                <span>{{ $device->branch->name ?? 'Belum diatur' }}</span>
            </p>
        </div>
        <span class="fingerprint-device-card__status {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>

    <p class="fingerprint-device-card__meta">
        <span>
            IP
            @if($device->ip_address)
                <strong class="fingerprint-device-card__mono">{{ $device->ip_address }}</strong>
            @else
                <strong class="fingerprint-device-card__warn">Belum diisi</strong>
            @endif
        </span>
        <span>Log <strong>{{ $device->logs_count }}</strong></span>
        <span>{{ $device->last_seen_at?->format('d/m H:i') ?? '—' }}</span>
    </p>

    <div class="fingerprint-device-card__actions">
        @include('partials.table-actions', [
            'module' => 'fingerprint_devices',
            'edit' => route('fingerprint-devices.edit', $device),
            'editLabel' => 'Kelola',
            'layout' => 'bar',
        ])
    </div>
</article>
