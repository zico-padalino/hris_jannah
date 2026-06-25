<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.login') }} — {{ $appBranding->name() }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="login-page easy-read min-h-screen antialiased">
    <div class="login-page__backdrop" aria-hidden="true">
        <div class="login-page__glow login-page__glow--1"></div>
        <div class="login-page__glow login-page__glow--2"></div>
    </div>

    <div class="login-page__shell">
        <div class="login-card">
            <div class="login-card__accent" aria-hidden="true"></div>

            <div class="login-card__toolbar">
                @include('partials.language-switcher')
            </div>

            <div class="login-card__brand">
                @include('partials.app-branding', [
                    'align' => 'center',
                    'class' => 'login-brand',
                    'nameClass' => 'login-brand__name text-xl font-extrabold sm:text-2xl',
                    'logoClass' => 'login-brand__logo',
                ])
                <p class="login-card__subtitle">{{ __('auth.subtitle') }}</p>
            </div>

            <div class="login-card__body">
                @include('partials.alerts')

                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf
                    <div class="login-form__field">
                        <label for="email" class="form-label">{{ __('auth.email') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            class="login-form__input w-full"
                            placeholder="nama@rs.local"
                        >
                    </div>
                    <div class="login-form__field">
                        <label for="password" class="form-label">{{ __('auth.password') }}</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="login-form__input w-full"
                            placeholder="••••••••"
                        >
                    </div>
                    <label class="login-form__remember">
                        <input type="checkbox" name="remember">
                        <span>{{ __('auth.remember') }}</span>
                    </label>
                    <button type="submit" class="btn-primary login-form__submit w-full">
                        {{ __('auth.submit') }}
                    </button>
                </form>

                <div class="login-demo">
                    <p class="login-demo__title">{{ __('auth.demo_title') }}</p>
                    <ul class="login-demo__list">
                        <li><span class="login-demo__email">admin@rs.local</span> — {{ __('enums.user_role.super_admin') }}</li>
                        <li><span class="login-demo__email">hrd@rs.local</span> — {{ __('enums.user_role.hr') }}</li>
                        <li><span class="login-demo__email">admin.serang@rs.local</span> — {{ __('enums.user_role.branch_admin') }}</li>
                        <li><span class="login-demo__email">budi@rs.local</span> — {{ __('enums.user_role.employee') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
