<span id="header-live-clock" class="inline-flex flex-wrap items-center gap-x-1.5" data-timezone="{{ config('app.timezone') }}">
    <span>{{ now()->locale(app()->getLocale())->translatedFormat('l, d F Y') }}</span>
    <span aria-hidden="true">|</span>
    <span data-clock-time class="font-semibold tabular-nums tracking-wide">{{ now()->format('H:i:s') }}</span>
    <span>{{ __('app.wib') }}</span>
</span>
