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
        'teal' => ['bg' => 'bg-teal-50 dark:bg-teal-950/40', 'icon' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/60 dark:text-teal-200', 'value' => 'text-teal-900 dark:text-teal-300'],
        'sky' => ['bg' => 'bg-sky-50 dark:bg-sky-950/40', 'icon' => 'bg-sky-100 text-sky-800 dark:bg-sky-900/60 dark:text-sky-200', 'value' => 'text-sky-900 dark:text-sky-300'],
        'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/40', 'icon' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200', 'value' => 'text-emerald-900 dark:text-emerald-300'],
        'orange' => ['bg' => 'bg-orange-50 dark:bg-orange-950/40', 'icon' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/60 dark:text-orange-200', 'value' => 'text-orange-900 dark:text-orange-300'],
        'red' => ['bg' => 'bg-red-50 dark:bg-red-950/40', 'icon' => 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200', 'value' => 'text-red-900 dark:text-red-300'],
        'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-950/40', 'icon' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200', 'value' => 'text-amber-900 dark:text-amber-300'],
        'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-950/40', 'icon' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/60 dark:text-violet-200', 'value' => 'text-violet-900 dark:text-violet-300'],
    ];
    $palette = $tones[$tone] ?? $tones['teal'];
@endphp

@php($tag = $href ? 'a' : 'div')

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    @class([
        'panel group relative block overflow-hidden p-5 transition',
        'hover:border-teal-600 hover:shadow-md dark:hover:border-teal-400' => $href,
        $palette['bg'] => true,
    ])
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-base font-bold text-slate-700 dark:text-slate-200">{{ $label }}</p>
            <p @class(['mt-2 text-3xl font-extrabold tracking-tight', $palette['value']])>{{ $value }}</p>
            @if($hint)
                <p class="mt-1.5 text-sm font-semibold text-slate-600 dark:text-slate-400">{{ $hint }}</p>
            @endif
        </div>
        <div @class(['flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border-2 border-current/20', $palette['icon']])>
            {!! $icon !!}
        </div>
    </div>
</{{ $tag }}>
