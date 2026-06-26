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
        'nav-link-mobile--notified' => $mobile && $count > 0 && ! $active,
        'nav-link-mobile--notified-active' => $mobile && $count > 0 && $active,
        $activeClass => $active,
        $inactiveClass => ! $active && ! $mobile,
        'nav-link-mobile:hover' => ! $active && $mobile && $count === 0,
    ])
>
    <span class="flex min-w-0 items-center gap-2">
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
