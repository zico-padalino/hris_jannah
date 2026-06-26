@auth
    <div id="mobile-nav-overlay" class="mobile-nav-overlay lg:hidden" aria-hidden="true">
        <div id="mobile-nav-backdrop" class="mobile-nav-overlay__backdrop"></div>
        <nav
            id="mobile-nav-menu"
            class="app-mobile-menu"
            aria-label="{{ __('app.menu') }}"
        >
            <div class="app-mobile-menu__head sidebar-brand flex items-center justify-between border-b-2 px-3 py-3">
                <div class="flex min-w-0 items-center gap-2">
                    @if($appBranding->hasLogo())
                        <img src="{{ $appBranding->logoUrl() }}" alt="" class="sidebar-brand__logo h-8 w-auto max-w-[4rem] shrink-0 object-contain">
                    @endif
                    <div class="min-w-0">
                        <p class="sidebar-brand__name truncate text-sm font-bold">{{ $appBranding->name() }}</p>
                        <p class="text-xs font-semibold" style="color: var(--app-text-muted)">{{ auth()->user()->role->label() }}</p>
                    </div>
                </div>
                <button type="button" id="mobile-nav-close" class="app-mobile-menu__close inline-flex min-h-9 min-w-9 items-center justify-center rounded-md" aria-label="{{ __('app.close_menu') }}">
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
