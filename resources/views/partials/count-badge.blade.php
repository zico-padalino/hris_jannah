@php
    $count = $count ?? 0;
    $variant = $variant ?? 'default';
    $label = $label ?? null;
    $pulse = $pulse ?? false;
@endphp

@if($count > 0)
    @php
        $display = $count > 99 ? '99+' : $count;
    @endphp

    @if($variant === 'dot')
        <span class="app-count-badge__dot-wrap" title="{{ $label ?? $display.' notifikasi' }}">
            @if($pulse)
                <span class="app-count-badge__ping"></span>
            @endif
            <span class="app-count-badge app-count-badge--dot"></span>
        </span>
    @elseif($variant === 'sidebar')
        <span @class([
            'app-count-badge app-count-badge--sidebar',
            'leave-badge-pulse' => $pulse,
        ])>
            {{ $display }}
            @if($label)
                <span class="font-semibold">{{ $label }}</span>
            @endif
        </span>
    @elseif($variant === 'pill')
        <span @class([
            'app-count-badge app-count-badge--pill',
            'leave-badge-pulse' => $pulse,
        ])>
            @if($pulse)
                <span class="relative flex h-2 w-2">
                    <span class="app-count-badge__ping"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full" style="background-color: var(--app-primary-text);"></span>
                </span>
            @endif
            {{ $display }}
            @if($label)
                <span>{{ $label }}</span>
            @endif
        </span>
    @else
        <span @class([
            'app-count-badge app-count-badge--default',
            'leave-badge-pulse' => $pulse,
        ])>
            {{ $display }}
        </span>
    @endif
@endif
