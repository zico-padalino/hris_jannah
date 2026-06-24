@php
    $large = $large ?? false;
    $badgeClass = $large ? 'badge-readable' : 'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium';
    $styles = [
        'check_in' => $large ? 'bg-sky-100 text-sky-900' : 'bg-sky-100 text-sky-800',
        'check_out' => $large ? 'bg-violet-100 text-violet-900' : 'bg-violet-100 text-violet-800',
        'leave' => $large ? 'bg-slate-200 text-slate-800' : 'bg-slate-100 text-slate-700',
    ];
    $class = $styles[$attendance->type->value] ?? ($large ? 'bg-slate-200 text-slate-800' : 'bg-slate-100 text-slate-700');
@endphp
<span class="{{ $badgeClass }} {{ $class }}">
    {{ $attendance->type->label() }}
</span>
