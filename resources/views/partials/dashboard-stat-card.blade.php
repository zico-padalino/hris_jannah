@props([
    'label',
    'value',
    'icon',
    'tone' => 'teal',
    'href' => null,
    'hint' => null,
])

@php
    $tones = [
        'teal' => 'dashboard-stat-card--teal',
        'sky' => 'dashboard-stat-card--sky',
        'emerald' => 'dashboard-stat-card--emerald',
        'campfire' => 'dashboard-stat-card--campfire',
        'orange' => 'dashboard-stat-card--orange',
        'red' => 'dashboard-stat-card--red',
        'amber' => 'dashboard-stat-card--campfire',
        'violet' => 'dashboard-stat-card--violet',
    ];
    $bgClass = $tones[$tone] ?? $tones['teal'];
@endphp

@php($tag = $href ? 'a' : 'div')

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @class([
        'dashboard-stat-card panel group relative block overflow-hidden p-5 transition',
        'hover:border-teal-600 hover:shadow-md dark:hover:border-teal-400' => $href,
        $bgClass => true,
    ])
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="dashboard-stat-card__label text-base font-bold">{{ $label }}</p>
            <p class="dashboard-stat-card__value mt-2 text-4xl font-extrabold tracking-tight">{{ $value }}</p>
            @if($hint)
                <p class="dashboard-stat-card__hint mt-1.5 text-sm font-semibold">{{ $hint }}</p>
            @endif
        </div>
        <div class="dashboard-stat-card__icon flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 border-current/20">
            {!! $icon !!}
        </div>
    </div>
</{{ $tag }}>
