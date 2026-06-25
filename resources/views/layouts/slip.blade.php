<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('pages.payroll_slip.title') }} — {{ $employee->name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="payslip-page">
    <div class="payslip-toolbar no-print">
        <div class="payslip-toolbar__main">
            <a href="{{ $backUrl }}" class="payroll-deduction-back payslip-toolbar__btn">
                <span class="payroll-deduction-back__icon" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </span>
                <span class="payslip-toolbar__btn-label">{{ __('pages.payroll_slip.back') }}</span>
            </a>
            @yield('toolbar-extra')
        </div>
    </div>

    @yield('content')

    @stack('scripts')
</body>
</html>
