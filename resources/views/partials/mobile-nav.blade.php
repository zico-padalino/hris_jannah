@auth
    <div id="mobile-nav-overlay" class="mobile-nav-overlay lg:hidden" aria-hidden="true">
        <div id="mobile-nav-backdrop" class="mobile-nav-overlay__backdrop"></div>
        <nav
            id="mobile-nav-menu"
            class="app-mobile-menu"
            aria-label="{{ __('app.menu') }}"
        >
            <div class="app-mobile-menu__head sidebar-brand flex items-start justify-between gap-2 border-b-2">
                <div class="app-mobile-menu__brand flex min-w-0 flex-1 items-center gap-2.5">
                    @if($appBranding->hasLogo())
                        <img src="{{ $appBranding->logoUrl() }}" alt="" class="app-mobile-menu__logo sidebar-brand__logo shrink-0 object-contain">
                    @endif
                    <div class="app-mobile-menu__brand-text min-w-0 flex-1">
                        @include('partials.brand-name-stacked', ['class' => 'font-bold'])
                        <p class="app-mobile-menu__role text-xs font-semibold" style="color: var(--app-text-muted)">{{ auth()->user()->role->label() }}</p>
                    </div>
                </div>
                <button type="button" id="mobile-nav-close" class="app-mobile-menu__close shrink-0" aria-label="{{ __('app.close_menu') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 space-y-1 p-3">
                @include('partials.sidebar-nav', ['mobile' => true])
            </div>

            <div class="app-mobile-menu__footer space-y-3 border-t-2 p-3" style="border-color: var(--app-sidebar-border)">
                @include('partials.user-preferences')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full">{{ __('app.logout') }}</button>
                </form>
            </div>
        </nav>
    </div>
@endauth
