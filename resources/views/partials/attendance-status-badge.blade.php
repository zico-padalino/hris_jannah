@php
    $large = $large ?? false;
    $labels = [
        'valid' => ['Tepat waktu', 'attendance-status-badge--valid'],
        'late' => ['Terlambat', 'attendance-status-badge--late'],
        'invalid_face' => ['Wajah tidak cocok', 'attendance-status-badge--invalid'],
        'invalid_location' => ['Lokasi tidak valid', 'attendance-status-badge--warning'],
        'invalid_both' => ['Wajah & lokasi invalid', 'attendance-status-badge--invalid'],
    ];
    [$text, $toneClass] = $labels[$attendance->status->value] ?? [$attendance->status->label(), 'attendance-status-badge--neutral'];
    $badgeClass = trim('attendance-status-badge '.($large ? 'attendance-status-badge--large ' : '').$toneClass);
@endphp
<span class="{{ $badgeClass }}">
    {{ $text }}
    @if($attendance->is_late && $attendance->late_minutes)
        <span class="attendance-status-badge__meta">· {{ $attendance->late_minutes }} mnt</span>
    @endif
</span>
