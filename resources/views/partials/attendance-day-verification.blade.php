@php
    $large = $large ?? false;
    $source = $attendance->source ?? \App\Enums\AttendanceSource::Face;
@endphp

@if($source === \App\Enums\AttendanceSource::Leave)
    <span @class(['text-slate-500', 'empty-dash' => $large, 'text-xs' => ! $large])>—</span>
@elseif($source === \App\Enums\AttendanceSource::Face)
    <div @class(['attendance-day-verification', 'attendance-day-verification--large' => $large, 'attendance-day-verification--compact' => ! $large])>
        @if($attendance->hasPhoto())
            @include('partials.attendance-photo', [
                'attendance' => $attendance,
                'size' => $large ? 'lg' : 'sm',
            ])
        @endif
        @if($attendance->face_match_score || $attendance->distance_meters)
            <div class="attendance-day-verification__meta">
                @if($attendance->face_match_score)
                    <p>Skor {{ number_format($attendance->face_match_score * 100, 1) }}%</p>
                @endif
                @if($attendance->distance_meters)
                    <p>Jarak {{ $attendance->distance_meters }} m</p>
                @endif
            </div>
        @elseif($large && ! $attendance->hasPhoto())
            <span class="attendance-day-verification__fallback">Wajah</span>
        @endif
    </div>
@elseif($source === \App\Enums\AttendanceSource::Fingerprint)
    <span @class([
        'inline-flex items-center gap-1.5 font-semibold text-indigo-800',
        'text-sm' => $large,
        'text-[11px] text-indigo-600' => ! $large,
    ])>
        <svg @class(['text-indigo-600', 'h-5 w-5' => $large, 'h-3.5 w-3.5' => ! $large]) fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" />
        </svg>
        Sidik jari
    </span>
@else
    <span @class(['font-semibold text-slate-700', 'text-sm' => $large, 'text-[11px] text-slate-500' => ! $large])>Input manual</span>
@endif
