<div class="user-preferences">
    <div class="user-preferences__row">
        <span class="user-preferences__label">{{ __('app.theme') }}</span>
        @include('partials.theme-toggle', ['compact' => true])
    </div>
    <div class="user-preferences__row">
        <span class="user-preferences__label">{{ __('app.language') }}</span>
        @include('partials.language-switcher', ['variant' => 'menu'])
    </div>
</div>
