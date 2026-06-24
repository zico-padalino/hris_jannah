<span class="header-live-clock inline-flex max-w-full flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[0.8125rem] leading-snug sm:text-sm" data-live-clock data-timezone="{{ config('app.timezone') }}">
    <span>{{ now()->locale(app()->getLocale())->translatedFormat('l, d F Y') }}</span>
    <span class="header-live-clock__divider" aria-hidden="true">|</span>
    <span data-clock-time class="font-semibold tabular-nums tracking-wide">{{ now()->format('H:i:s') }}</span>
    <span>{{ __('app.wib') }}</span>
</span>
