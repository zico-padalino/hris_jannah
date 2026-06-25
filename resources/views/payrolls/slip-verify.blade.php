<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('pages.payroll_slip.verify_title') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="slip-verify-page">
    <main class="slip-verify-card panel">
        <div class="slip-verify-card__badge">{{ __('pages.payroll_slip.verify_valid') }}</div>
        <h1 class="slip-verify-card__title">{{ __('pages.payroll_slip.verify_title') }}</h1>
        <p class="slip-verify-card__subtitle">{{ $verification['period_name'] }}</p>

        <dl class="slip-verify-list">
            <div>
                <dt>{{ __('pages.payroll_slip.scan_hrd_label') }}</dt>
                <dd>{{ $verification['hrd_name'] }}</dd>
            </div>
            <div>
                <dt>{{ __('pages.payroll_slip.scan_employee_label') }}</dt>
                <dd>{{ $verification['employee_name'] }}</dd>
            </div>
            <div>
                <dt>{{ __('pages.payroll_slip.scan_employee_number_label') }}</dt>
                <dd>{{ $verification['employee_number'] }}</dd>
            </div>
            <div>
                <dt>{{ __('pages.payroll_slip.scan_slip_number_label') }}</dt>
                <dd class="slip-verify-list__code">{{ $verification['verification_code'] }}</dd>
            </div>
        </dl>
    </main>
</body>
</html>
