<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.login') }} — {{ $appBranding->name() }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="easy-read min-h-screen bg-gradient-to-br from-teal-900 via-teal-800 to-slate-900">
    <div class="flex min-h-screen items-center justify-center px-4 py-8">
        <div class="panel w-full max-w-md p-8">
            <div class="mb-6 flex justify-end">
                @include('partials.language-switcher')
            </div>
            <div class="mb-8">
                @include('partials.app-branding', [
                    'align' => 'center',
                    'nameClass' => 'text-3xl font-extrabold text-slate-900',
                    'logoClass' => 'h-16 w-auto max-w-[220px] object-contain',
                ])
                <p class="mt-3 text-center text-base font-semibold text-slate-600">{{ __('auth.subtitle') }}</p>
            </div>

            @include('partials.alerts')

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="form-label">{{ __('auth.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                        class="w-full">
                </div>
                <div>
                    <label for="password" class="form-label">{{ __('auth.password') }}</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full">
                </div>
                <label class="flex items-center gap-3 text-base font-semibold text-slate-700">
                    <input type="checkbox" name="remember">
                    {{ __('auth.remember') }}
                </label>
                <button type="submit" class="btn-primary w-full">
                    {{ __('auth.submit') }}
                </button>
            </form>

            <div class="panel mt-8 border-slate-300 bg-slate-50 p-5 text-base text-slate-700">
                <p class="font-bold text-slate-900">{{ __('auth.demo_title') }}</p>
                <ul class="mt-3 space-y-1.5 font-medium">
                    <li>admin@rs.local — {{ __('enums.user_role.super_admin') }}</li>
                    <li>hrd@rs.local — {{ __('enums.user_role.hr') }}</li>
                    <li>admin.serang@rs.local — {{ __('enums.user_role.branch_admin') }}</li>
                    <li>budi@rs.local — {{ __('enums.user_role.employee') }}</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
