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
        use App\Enums\SidebarNavItem;

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
                <div class="shrink-0 border-b-2 border-inherit px-6 py-5" style="border-color: var(--app-sidebar-border)">
                    @include('partials.app-branding', [
                        'nameClass' => 'text-xl font-bold',
                        'logoClass' => 'h-10 w-auto max-w-[160px] object-contain',
                    ])
                </div>
                <nav class="sidebar-nav-scroll flex-1 space-y-0.5 overflow-y-auto p-4" aria-label="Menu utama">
                    @include('partials.sidebar-nav', ['mobile' => false])
                </nav>
                <div class="shrink-0 border-t-2 p-4" style="border-color: var(--app-sidebar-border)">
                    <p class="truncate text-sm font-bold text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs font-semibold" style="color: var(--app-sidebar-text-muted)">{{ auth()->user()->role->label() }}</p>
                </div>
            </aside>
        @endauth

        <div class="flex min-h-0 min-w-0 flex-1 flex-col lg:h-screen lg:overflow-hidden">
            @include('partials.mobile-nav')

            @auth
                <header class="app-header hidden shrink-0 border-b-2 px-4 py-3 lg:block lg:px-8">
                    <div class="app-header__inner">
                        <h1 class="page-title app-header__title">@yield('title', __('nav.dashboard'))</h1>
                        <div class="app-header__actions">
                            @if($sidebar->visible(auth()->user(), SidebarNavItem::LeaveApproval) && $pendingLeaveApprovalCount > 0)
                                <a
                                    href="{{ route('leave-approvals.index', ['status' => 'pending']) }}"
                                    class="leave-badge-pulse app-header__leave-badge inline-flex shrink-0 items-center rounded-lg border-2 border-amber-500 bg-amber-100 px-3 py-1.5 text-sm font-bold text-amber-900 hover:bg-amber-200 dark:border-amber-400 dark:bg-amber-950 dark:text-amber-100 dark:hover:bg-amber-900"
                                >
                                    {{ __('app.new_requests', ['count' => $pendingLeaveApprovalCount]) }}
                                </a>
                            @endif
                            <div class="app-header__user-cluster">
                                @include('partials.user-account-menu')
                            </div>
                        </div>
                    </div>
                </header>

                <div class="app-header app-mobile-bar min-w-0 shrink-0 border-b-2 px-3 py-2.5 sm:px-4 sm:py-3 lg:hidden">
                    <h1 class="page-title text-lg sm:text-xl">@yield('title', __('nav.dashboard'))</h1>
                    @hasSection('subtitle')
                        <p class="page-subtitle text-sm sm:text-base">@yield('subtitle')</p>
                    @endif
                </div>
            @endauth

            <main class="flex-1 min-h-0 min-w-0 overflow-y-auto px-3 py-4 sm:px-4 sm:py-6 lg:px-8">
                @include('partials.alerts')
                @hasSection('subtitle')
                    <p class="page-subtitle page-subtitle--main mb-4 hidden lg:block">@yield('subtitle')</p>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
