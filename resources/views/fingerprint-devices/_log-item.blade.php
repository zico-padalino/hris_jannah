@php
    $statusClass = match ($log->process_status) {
        'processed' => 'fingerprint-log-item__status--success',
        'failed' => 'fingerprint-log-item__status--failed',
        default => 'fingerprint-log-item__status--pending',
    };
    $statusLabel = match ($log->process_status) {
        'processed' => 'Berhasil',
        'failed' => 'Gagal',
        default => $log->process_status,
    };
    $note = $log->process_message ?? ($log->attendance_id ? 'Masuk ke absensi #'.$log->attendance_id : null);
@endphp

<article class="fingerprint-log-item panel">
    <div class="fingerprint-log-item__head">
        <time class="fingerprint-log-item__time">{{ $log->punched_at->format('d/m/Y H:i') }}</time>
        <span class="fingerprint-log-item__status {{ $statusClass }}">{{ $statusLabel }}</span>
    </div>
    <p class="fingerprint-log-item__main">
        <span class="fingerprint-log-item__name">{{ $log->employee->name ?? '—' }}</span>
        <span class="fingerprint-log-item__pin">PIN {{ $log->device_pin }}</span>
    </p>
    @if($note)
        <p class="fingerprint-log-item__note">{{ $note }}</p>
    @endif
</article>
