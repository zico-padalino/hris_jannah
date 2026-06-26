@php
    $branding = $appBranding ?? app(\App\Services\AppBrandingService::class);
    $lines = $branding->nameLines();
@endphp

<span @class(['sidebar-brand__name sidebar-brand__name--stacked', $class ?? null])>
    <span class="sidebar-brand__name-line">{{ $lines['first'] }}</span>
    @if($lines['second'])
        <span class="sidebar-brand__name-line">{{ $lines['second'] }}</span>
    @endif
</span>
