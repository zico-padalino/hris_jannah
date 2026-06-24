@php
    $current = app()->getLocale();
@endphp
<div class="theme-switcher-shell flex items-center gap-1" role="group" aria-label="{{ __('app.language') }}">
    @foreach(['id' => __('app.indonesian'), 'en' => __('app.english')] as $code => $label)
        <form method="POST" action="{{ route('locale.update') }}" class="inline">
            @csrf
            <input type="hidden" name="locale" value="{{ $code }}">
            <button
                type="submit"
                data-active="{{ $current === $code ? 'true' : 'false' }}"
                class="rounded-md px-2.5 py-1.5 text-xs font-bold transition sm:px-3 sm:text-sm"
                aria-pressed="{{ $current === $code ? 'true' : 'false' }}"
            >
                {{ strtoupper($code) }}
            </button>
        </form>
    @endforeach
</div>
