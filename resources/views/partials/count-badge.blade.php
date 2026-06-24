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
        <span class="relative inline-flex h-3 w-3 shrink-0" title="{{ $label ?? $display.' notifikasi' }}">
            @if($pulse)
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
            @endif
            <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500 ring-2 ring-white"></span>
        </span>
    @elseif($variant === 'sidebar')
        <span @class([
            'inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-500 px-2 py-0.5 text-[11px] font-bold leading-none text-white shadow-sm ring-2 ring-amber-300/50',
            'leave-badge-pulse' => $pulse,
        ])>
            @if($pulse)
                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
            @endif
            {{ $display }}
            @if($label)
                <span class="font-semibold">{{ $label }}</span>
            @endif
        </span>
    @elseif($variant === 'pill')
        <span @class([
            'inline-flex items-center gap-1.5 rounded-full bg-amber-500 px-3 py-1 text-xs font-bold text-white shadow-md',
            'leave-badge-pulse' => $pulse,
        ])>
            @if($pulse)
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                </span>
            @endif
            {{ $display }}
            @if($label)
                <span>{{ $label }}</span>
            @endif
        </span>
    @else
        <span @class([
            'inline-flex min-w-6 items-center justify-center rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold leading-none text-white shadow-sm ring-2 ring-amber-200',
            'leave-badge-pulse' => $pulse,
        ])>
            {{ $display }}
        </span>
    @endif
@endif
