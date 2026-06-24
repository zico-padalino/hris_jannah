@auth
    <div class="app-mobile-topbar app-mobile-bar sticky top-0 z-40 border-b-2 lg:hidden">
        <div class="flex min-w-0 items-center justify-between gap-2 px-3 py-2.5 sm:gap-3 sm:px-4 sm:py-3">
            <div class="flex min-w-0 flex-1 items-center gap-2">
                @if($appBranding->hasLogo())
                    <img src="{{ $appBranding->logoUrl() }}" alt="{{ $appBranding->name() }}" class="h-7 w-auto max-w-[72px] shrink-0 object-contain sm:h-8 sm:max-w-[96px]">
                @endif
                <div class="min-w-0">
                    <p class="truncate text-base font-bold sm:text-lg">{{ $appBranding->name() }}</p>
                    <p class="truncate text-xs font-semibold app-muted-text sm:text-sm">{{ auth()->user()->name }}</p>
                </div>
            </div>
            <div class="mobile-toolbar flex shrink-0 items-center gap-1.5 sm:gap-2">
                @include('partials.theme-toggle')
                @include('partials.language-switcher')
                <button
                    type="button"
                    id="mobile-nav-toggle"
                    class="inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-lg border-2"
                    style="background-color: var(--app-surface-muted); border-color: var(--app-border-strong); color: var(--app-text)"
                    aria-expanded="false"
                    aria-controls="mobile-nav-menu"
                    aria-label="{{ __('app.open_menu') }}"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-nav-overlay" class="fixed inset-0 z-50 hidden lg:hidden" aria-hidden="true">
        <div id="mobile-nav-backdrop" class="absolute inset-0" style="background-color: var(--app-overlay)"></div>
        <nav
            id="mobile-nav-menu"
            class="app-mobile-menu absolute right-0 top-0 flex h-full w-full max-w-sm flex-col overflow-y-auto shadow-2xl sm:max-w-xs"
            aria-label="{{ __('app.menu') }}"
        >
            <div class="flex items-center justify-between border-b-2 px-4 py-4 text-white" style="background-color: var(--app-sidebar-bg); border-color: var(--app-sidebar-border)">
                <div>
                    <p class="text-lg font-bold">{{ __('app.menu') }}</p>
                    <p class="text-sm" style="color: var(--app-sidebar-text-muted)">{{ auth()->user()->role->label() }}</p>
                </div>
                <button type="button" id="mobile-nav-close" class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-lg" style="background-color: var(--app-sidebar-active)" aria-label="{{ __('app.close_menu') }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-1 space-y-1 p-3">
                @include('partials.sidebar-nav', ['mobile' => true])
            </div>

            <div class="space-y-3 border-t-2 p-3" style="border-color: var(--app-border)">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full">{{ __('app.logout') }}</button>
                </form>
            </div>
        </nav>
    </div>

    @push('scripts')
        <script>
            (function () {
                const toggle = document.getElementById('mobile-nav-toggle');
                const close = document.getElementById('mobile-nav-close');
                const overlay = document.getElementById('mobile-nav-overlay');
                const backdrop = document.getElementById('mobile-nav-backdrop');

                function openMenu() {
                    overlay?.classList.remove('hidden');
                    overlay?.setAttribute('aria-hidden', 'false');
                    toggle?.setAttribute('aria-expanded', 'true');
                    document.body.classList.add('overflow-hidden');
                }

                function closeMenu() {
                    overlay?.classList.add('hidden');
                    overlay?.setAttribute('aria-hidden', 'true');
                    toggle?.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('overflow-hidden');
                }

                toggle?.addEventListener('click', openMenu);
                close?.addEventListener('click', closeMenu);
                backdrop?.addEventListener('click', closeMenu);
                overlay?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
            })();
        </script>
    @endpush
@endauth
