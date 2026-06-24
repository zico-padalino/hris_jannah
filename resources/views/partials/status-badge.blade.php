@php
    $labels = [
        'valid' => ['Valid', 'bg-emerald-100 text-emerald-800'],
        'invalid_face' => ['Wajah Invalid', 'bg-red-100 text-red-800'],
        'invalid_location' => ['Lokasi Invalid', 'bg-amber-100 text-amber-800'],
        'invalid_both' => ['Wajah & Lokasi Invalid', 'bg-red-100 text-red-800'],
    ];
    [$text, $class] = $labels[$status->value] ?? [$status->label(), 'bg-slate-100 text-slate-800'];
@endphp
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $class }}">{{ $text }}</span>
