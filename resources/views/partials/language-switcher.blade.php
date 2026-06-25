@php
    $current = app()->getLocale();
    $variant = $variant ?? 'default';
@endphp
<div @class([
    'locale-switcher',
    'locale-switcher--menu' => $variant === 'menu',
]) role="group" aria-label="{{ __('app.language') }}">
    @foreach(['id' => __('app.indonesian'), 'en' => __('app.english')] as $code => $label)
        <form method="POST" action="{{ route('locale.update') }}" class="locale-switcher__form">
            @csrf
            <input type="hidden" name="locale" value="{{ $code }}">
            <button
                type="submit"
                data-active="{{ $current === $code ? 'true' : 'false' }}"
                class="locale-switcher__btn"
                aria-pressed="{{ $current === $code ? 'true' : 'false' }}"
                title="{{ $label }}"
            >
                {{ strtoupper($code) }}
            </button>
        </form>
    @endforeach
</div>
