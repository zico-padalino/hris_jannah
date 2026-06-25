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
        <button type="button" class="payroll-deduction-back" onclick="window.print()">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 9v6.75M17.25 9v6.75M4.5 9.75h15a1.5 1.5 0 011.5 1.5v5.25a1.5 1.5 0 01-1.5 1.5H4.5a1.5 1.5 0 01-1.5-1.5v-5.25A1.5 1.5 0 014.5 9.75zM7.5 6.75h9V4.875A1.125 1.125 0 0015.375 3.75h-6.75A1.125 1.125 0 007.5 4.875V6.75z" />
                </svg>
            </span>
            <span>{{ __('pages.payroll_slip.print') }}</span>
        </button>
    </div>

    @yield('content')

    @stack('scripts')
</body>
</html>
