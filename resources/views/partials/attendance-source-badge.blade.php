@php
    $large = $large ?? false;
    $badgeClass = $large ? 'badge-readable' : 'inline-flex rounded-md px-2 py-0.5 text-xs font-medium';
    $source = $attendance->source ?? \App\Enums\AttendanceSource::Face;
    $styles = [
        'face' => $large ? 'bg-teal-100 text-teal-900' : 'bg-teal-50 text-teal-700 ring-1 ring-teal-200',
        'fingerprint' => $large ? 'bg-indigo-100 text-indigo-900' : 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200',
        'gps' => $large ? 'bg-amber-100 text-amber-900' : 'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
        'manual' => $large ? 'bg-amber-100 text-amber-900' : 'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
        'leave' => $large ? 'bg-slate-200 text-slate-800' : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200',
    ];
    $class = $styles[$source->value] ?? ($large ? 'bg-slate-200 text-slate-800' : 'bg-slate-50 text-slate-600 ring-1 ring-slate-200');
@endphp
<span class="{{ $badgeClass }} {{ $class }}">
    {{ $source->label() }}
</span>
