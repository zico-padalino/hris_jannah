@php
    $large = $large ?? false;
    $labels = [
        'valid' => ['Tepat waktu', $large ? 'bg-emerald-100 text-emerald-900' : 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200'],
        'late' => ['Terlambat', $large ? 'bg-orange-100 text-orange-900' : 'bg-orange-100 text-orange-800 ring-1 ring-orange-200'],
        'invalid_face' => ['Wajah tidak cocok', $large ? 'bg-red-100 text-red-900' : 'bg-red-100 text-red-800 ring-1 ring-red-200'],
        'invalid_location' => ['Lokasi tidak valid', $large ? 'bg-amber-100 text-amber-900' : 'bg-amber-100 text-amber-800 ring-1 ring-amber-200'],
        'invalid_both' => ['Wajah & lokasi invalid', $large ? 'bg-red-100 text-red-900' : 'bg-red-100 text-red-800 ring-1 ring-red-200'],
    ];
    [$text, $class] = $labels[$attendance->status->value] ?? [$attendance->status->label(), $large ? 'bg-slate-200 text-slate-900' : 'bg-slate-100 text-slate-800 ring-1 ring-slate-200'];
    $badgeClass = $large ? 'badge-readable' : 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold';
@endphp
<span class="{{ $badgeClass }} {{ $class }}">
    {{ $text }}
    @if($attendance->is_late && $attendance->late_minutes)
        <span @class(['font-normal', 'opacity-90' => ! $large])>· {{ $attendance->late_minutes }} mnt</span>
    @endif
</span>
