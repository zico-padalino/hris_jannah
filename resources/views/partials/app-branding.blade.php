@php
    $branding = $appBranding ?? app(\App\Services\AppBrandingService::class);
    $align = $align ?? 'start';
    $layout = $layout ?? 'col';
    $showTagline = $showTagline ?? false;
@endphp

<div @class([
    'flex gap-2',
    'flex-col' => $layout === 'col',
    'flex-row items-center' => $layout === 'row',
    'items-center text-center' => $align === 'center' && $layout === 'col',
    'items-start text-left' => $align !== 'center' && $layout === 'col',
    'text-left' => $layout === 'row',
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
    <div @class([
        'w-full' => $align !== 'center' && $layout === 'col',
        'min-w-0 flex-1' => $layout === 'row',
    ])>
        @if($stackedName ?? false)
            @include('partials.brand-name-stacked', ['class' => $nameClass ?? 'text-xl font-bold'])
        @else
            <p @class([$nameClass ?? 'text-xl font-bold'])>{{ $branding->name() }}</p>
        @endif
        @if($showTagline)
            <p @class([$taglineClass ?? 'mt-1 text-base font-medium']) style="{{ $taglineStyle ?? '' }}">{{ __('app.tagline') }}</p>
        @endif
    </div>
</div>
