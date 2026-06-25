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
        <a href="{{ $backUrl }}" class="payroll-deduction-back">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </span>
            <span>{{ __('pages.payroll_slip.back') }}</span>
        </a>
        <button type="button" class="btn-primary" onclick="window.print()">{{ __('pages.payroll_slip.print') }}</button>
    </div>

    @yield('content')

    @stack('scripts')
</body>
</html>
