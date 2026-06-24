@props([
    'href',
    'count' => 0,
    'active' => false,
    'label',
    'pendingLabel' => 'Menunggu',
    'mobile' => false,
    'nested' => false,
])

@php
    $baseClass = $mobile ? 'nav-link-mobile' : 'nav-link';
    $activeClass = $mobile ? 'nav-link-mobile--active' : 'nav-link--active';
    $inactiveClass = $mobile ? '' : 'hover:bg-teal-800';
    $nestedClass = $nested ? ' nav-link--nested' : '';
@endphp

<a
    href="{{ $href }}"
    @class([
        $baseClass.$nestedClass,
        'flex items-center justify-between gap-2',
        'border-2 border-amber-400 bg-amber-50 text-amber-900' => $mobile && $count > 0 && ! $active,
        'border-2 border-amber-500 bg-amber-100 text-amber-900' => $mobile && $count > 0 && $active,
        $activeClass => $active,
        $inactiveClass => ! $active && ! $mobile,
        'nav-link-mobile:hover' => ! $active && $mobile && $count === 0,
    ])
>
    <span class="flex min-w-0 items-center gap-2">
        @if($count > 0)
            <span class="relative flex h-2.5 w-2.5 shrink-0">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-300 opacity-75"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-400"></span>
            </span>
        @endif
        <span @class(['truncate', 'font-semibold' => $count > 0])>{{ $label }}</span>
    </span>
    @if($count > 0)
        @include('partials.count-badge', [
            'count' => $count,
            'variant' => 'sidebar',
            'label' => $pendingLabel,
            'pulse' => true,
        ])
    @endif
</a>
