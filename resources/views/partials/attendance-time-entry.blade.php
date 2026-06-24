@php
    use App\Enums\AttendanceSource;
    use App\Enums\AttendanceType;

    $showTime = $showTime ?? true;
    $type = $attendance->type;
    $source = $attendance->source ?? AttendanceSource::Face;

    $typeStyles = [
        'check_in' => [
            'box' => 'attendance-time-entry--in',
            'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            'iconBg' => 'bg-sky-600',
        ],
        'check_out' => [
            'box' => 'attendance-time-entry--out',
            'icon' => 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9',
            'iconBg' => 'bg-violet-600',
        ],
        'leave' => [
            'box' => 'attendance-time-entry--leave',
            'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
            'iconBg' => 'bg-slate-600',
        ],
    ];

    $sourceLabels = [
        'face' => AttendanceSource::Face->label(),
        'fingerprint' => AttendanceSource::Fingerprint->label(),
        'manual' => AttendanceSource::Manual->label(),
        'leave' => AttendanceSource::Leave->label(),
    ];

    $style = $typeStyles[$type->value] ?? $typeStyles['leave'];
@endphp

<div @class(['attendance-time-entry', $style['box'], 'attendance-time-entry--no-time' => ! $showTime])>
    <div class="attendance-time-entry__main">
        <div class="attendance-time-entry__type">
            <span class="attendance-time-entry__icon {{ $style['iconBg'] }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $style['icon'] }}" />
                </svg>
            </span>
            <span class="attendance-time-entry__label">{{ $type->label() }}</span>
        </div>
        @if($showTime)
            <div class="attendance-time-entry__clock">
                <span class="attendance-time-entry__time">{{ $attendance->attended_at->format('H:i') }}</span>
                <span class="attendance-time-entry__suffix">{{ __('app.wib') }}</span>
            </div>
        @endif
    </div>
    <div class="attendance-time-entry__source">
        {{ $sourceLabels[$source->value] ?? $source->label() }}
    </div>
</div>
