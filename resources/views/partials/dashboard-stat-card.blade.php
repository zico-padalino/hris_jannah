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
        'teal' => ['bg' => 'dashboard-stat-card--teal', 'icon' => 'dashboard-stat-card__icon--teal'],
        'sky' => ['bg' => 'dashboard-stat-card--sky', 'icon' => 'dashboard-stat-card__icon--sky'],
        'emerald' => ['bg' => 'dashboard-stat-card--emerald', 'icon' => 'dashboard-stat-card__icon--emerald'],
        'campfire' => ['bg' => 'dashboard-stat-card--campfire', 'icon' => 'dashboard-stat-card__icon--campfire'],
        'orange' => ['bg' => 'dashboard-stat-card--orange', 'icon' => 'dashboard-stat-card__icon--orange'],
        'red' => ['bg' => 'dashboard-stat-card--red', 'icon' => 'dashboard-stat-card__icon--red'],
        'amber' => ['bg' => 'dashboard-stat-card--campfire', 'icon' => 'dashboard-stat-card__icon--campfire'],
        'violet' => ['bg' => 'dashboard-stat-card--violet', 'icon' => 'dashboard-stat-card__icon--violet'],
    ];
    $palette = $tones[$tone] ?? $tones['teal'];
@endphp

@php($tag = $href ? 'a' : 'div')

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @class([
        'dashboard-stat-card panel group relative block overflow-hidden p-5 transition',
        'hover:border-teal-600 hover:shadow-md dark:hover:border-teal-400' => $href,
        $palette['bg'] => true,
    ])
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="dashboard-stat-card__label text-base font-bold">{{ $label }}</p>
            <p class="dashboard-stat-card__value mt-2 text-3xl font-extrabold tracking-tight">{{ $value }}</p>
            @if($hint)
                <p class="dashboard-stat-card__hint mt-1.5 text-sm font-semibold">{{ $hint }}</p>
            @endif
        </div>
        <div @class(['dashboard-stat-card__icon flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 border-current/20', $palette['icon']])>
            {!! $icon !!}
        </div>
    </div>
</{{ $tag }}>
