<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('nav.dashboard')) — {{ $appBranding->name() }}</title>
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                if (stored === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="easy-read app-body min-h-screen antialiased lg:h-screen lg:overflow-hidden">
    @php
        $sidebar = $sidebar ?? app(\App\Services\SidebarService::class);
    @endphp
    <div @class([
        'flex min-h-screen lg:h-full',
        'lg:flex-row-reverse' => $sidebar->isRight(),
    ])>
        @auth
            <aside @class([
                'app-sidebar hidden w-72 shrink-0 text-white lg:flex lg:h-screen lg:flex-col lg:overflow-hidden',
                'border-l-2' => $sidebar->isRight(),
                'border-r-2' => ! $sidebar->isRight(),
            ])>
                <div class="sidebar-brand app-topbar shrink-0 border-b-2">
                    @include('partials.app-branding', [
                        'layout' => 'row',
                        'nameClass' => 'sidebar-brand__name font-bold',
                        'logoClass' => 'sidebar-brand__logo shrink-0',
                    ])
                </div>
                <nav class="sidebar-nav-scroll flex-1 space-y-0.5 overflow-y-auto p-4" aria-label="Menu utama">
                    @include('partials.sidebar-nav', ['mobile' => false])
                </nav>
                <div class="sidebar-footer shrink-0 border-t-2 p-4" style="border-color: var(--app-sidebar-border)">
                    <p class="sidebar-footer__text text-center text-xs font-semibold" style="color: var(--app-sidebar-text-muted)">
                        {{ __('app.copyright', ['year' => 2026]) }}
                    </p>
                </div>
            </aside>
        @endauth

        <div class="flex min-h-0 min-w-0 flex-1 flex-col lg:h-screen lg:overflow-hidden">
            @include('partials.mobile-nav')

            @auth
                <header class="app-header app-topbar sticky top-0 z-40 shrink-0 border-b-2 px-3 py-2.5 sm:px-4 sm:py-3 lg:px-8 lg:py-0">
                    <div class="app-header__inner">
                        <button
                            type="button"
                            id="mobile-nav-toggle"
                            class="app-header__menu-btn lg:hidden"
                            aria-expanded="false"
                            aria-controls="mobile-nav-menu"
                            aria-label="{{ __('app.open_menu') }}"
                            data-open-label="{{ __('app.open_menu') }}"
                            data-close-label="{{ __('app.close_menu') }}"
                        >
                            <span class="app-header__menu-icon" aria-hidden="true">
                                <span class="app-header__menu-line"></span>
                                <span class="app-header__menu-line"></span>
                                <span class="app-header__menu-line"></span>
                            </span>
                        </button>

                        <h1 class="page-title app-header__title">@yield('title', __('nav.dashboard'))</h1>

                        <div class="app-header__clock app-header__clock--desktop">
                            @include('partials.header-live-clock')
                        </div>

                        <div class="app-header__actions">
                            @include('partials.header-notifications')
                            <div class="app-header__user-cluster">
                                @include('partials.user-account-menu')
                            </div>
                        </div>
                    </div>

                    <div class="app-header__mobile-meta lg:hidden">
                        <div class="app-header__clock app-header__clock--mobile">
                            @include('partials.header-live-clock')
                        </div>
                    </div>
                </header>
            @endauth

            <main class="flex-1 min-h-0 min-w-0 overflow-y-auto px-3 py-4 sm:px-4 sm:py-6 lg:px-8">
                @include('partials.alerts')
                @hasSection('subtitle')
                    <p class="page-subtitle page-subtitle--main mb-4">@yield('subtitle')</p>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
