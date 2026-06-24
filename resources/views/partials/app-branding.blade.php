@php
    $branding = $appBranding ?? app(\App\Services\AppBrandingService::class);
    $align = $align ?? 'start';
    $showTagline = $showTagline ?? false;
@endphp

<div @class([
    'flex flex-col gap-2',
    'items-center text-center' => $align === 'center',
    'items-start text-left' => $align !== 'center',
    $class ?? '',
])>
    @if($branding->hasLogo())
        <img
            src="{{ $branding->logoUrl() }}"
            alt="{{ $branding->name() }}"
            @class([
                'object-contain',
                $logoClass ?? 'h-12 w-auto max-w-[180px]',
            ])
        >
    @endif
    <div @class(['w-full' => $align !== 'center'])>
        <p @class([$nameClass ?? 'text-xl font-bold'])>{{ $branding->name() }}</p>
        @if($showTagline)
            <p @class([$taglineClass ?? 'mt-1 text-base font-medium']) style="{{ $taglineStyle ?? '' }}">{{ __('app.tagline') }}</p>
        @endif
    </div>
</div>
