@php
    $status = $attendance->status;

    $statusStyles = [
        'valid' => [
            'box' => 'attendance-status-entry--valid',
            'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'iconBg' => 'bg-emerald-600',
            'hint' => __('attendance.on_schedule'),
        ],
        'late' => [
            'box' => 'attendance-status-entry--late',
            'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
            'iconBg' => 'bg-orange-600',
            'hint' => null,
        ],
        'invalid_face' => [
            'box' => 'attendance-status-entry--invalid',
            'icon' => 'M15.75 6V18M4.5 12h15M12 3v1.5m0 15V21',
            'iconBg' => 'bg-red-600',
            'hint' => __('attendance.face_failed'),
        ],
        'invalid_location' => [
            'box' => 'attendance-status-entry--invalid',
            'icon' => 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
            'iconBg' => 'bg-amber-600',
            'hint' => __('attendance.location_invalid'),
        ],
        'invalid_both' => [
            'box' => 'attendance-status-entry--invalid',
            'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
            'iconBg' => 'bg-red-700',
            'hint' => __('attendance.both_failed'),
        ],
    ];

    $style = $statusStyles[$status->value] ?? [
        'box' => 'attendance-status-entry--neutral',
        'icon' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
        'iconBg' => 'bg-slate-600',
        'hint' => null,
    ];

    $lateMinutes = $attendance->is_late && $attendance->late_minutes ? $attendance->late_minutes : null;
    $hint = match (true) {
        $lateMinutes !== null => __('attendance.past_schedule'),
        filled($style['hint'] ?? null) => $style['hint'],
        default => null,
    };
@endphp

<div class="attendance-status-entry {{ $style['box'] }}">
    <div class="attendance-status-entry__main">
        <span class="attendance-status-entry__icon {{ $style['iconBg'] }}">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $style['icon'] }}" />
            </svg>
        </span>
        <div class="attendance-status-entry__text">
            <span class="attendance-status-entry__label">{{ $status->label() }}</span>
            @if($lateMinutes)
                <span class="attendance-status-entry__detail">{{ __('attendance.minutes', ['count' => $lateMinutes]) }}</span>
            @endif
        </div>
    </div>
    @if($hint)
        <div class="attendance-status-entry__hint">{{ $hint }}</div>
    @endif
</div>
