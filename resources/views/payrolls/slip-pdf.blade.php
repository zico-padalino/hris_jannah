<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('pages.payroll_slip.title') }} — {{ $employee->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            color: #1e293b;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.35;
            padding: 24px 28px;
        }
        .header {
            border-bottom: 1px solid #cbd5e1;
            margin-bottom: 12px;
            padding-bottom: 10px;
            width: 100%;
        }
        .header td { vertical-align: top; }
        .brand { width: 65%; }
        .meta { text-align: right; width: 35%; }
        .logo { height: 36px; margin-bottom: 4px; }
        .org { color: #c8510f; font-size: 8px; font-weight: bold; letter-spacing: 0.04em; text-transform: uppercase; }
        .title { font-size: 16px; font-weight: bold; margin-top: 2px; }
        .period { color: #64748b; font-size: 9px; margin-top: 2px; }
        .meta-label { color: #64748b; display: block; font-size: 8px; }
        .meta-value { display: block; font-size: 9px; font-weight: bold; margin-top: 1px; word-break: break-all; }
        .section { margin-top: 10px; }
        .section-title { font-size: 10px; font-weight: bold; margin-bottom: 5px; }
        .info-table { border-collapse: collapse; width: 100%; }
        .info-table td { padding: 2px 4px 2px 0; vertical-align: top; width: 33.33%; }
        .info-label { color: #64748b; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .info-value { font-size: 9px; font-weight: bold; margin-top: 1px; }
        .data-table { border: 1px solid #cbd5e1; border-collapse: collapse; margin-top: 4px; width: 100%; }
        .data-table td { border-bottom: 1px solid #e2e8f0; font-size: 9px; padding: 4px 6px; }
        .data-table tr:last-child td { border-bottom: none; }
        .amount { font-weight: bold; text-align: right; white-space: nowrap; }
        .deduction { color: #b91c1c; }
        .subtotal td { background: #f8fafc; font-weight: bold; }
        .net {
            background: #fff7ed;
            border: 1px solid #fdba74;
            border-radius: 4px;
            margin-top: 10px;
            padding: 8px 10px;
            width: 100%;
        }
        .net td { font-size: 10px; font-weight: bold; }
        .net-amount { color: #c2410c; font-size: 12px; text-align: right; }
        .notes { color: #64748b; font-size: 8px; margin-top: 8px; }
        .footer {
            border-top: 1px dashed #94a3b8;
            margin-top: 14px;
            padding-top: 10px;
            text-align: center;
            width: 100%;
        }
        .footer-label { color: #64748b; font-size: 8px; font-weight: bold; letter-spacing: 0.04em; text-transform: uppercase; }
        .signature-img { height: 42px; margin: 4px auto; }
        .qr { display: block; height: 100px; margin: 4px auto; width: 100px; }
        .slip-code { color: #64748b; font-family: DejaVu Sans Mono, monospace; font-size: 7px; margin-top: 3px; word-break: break-all; }
        .sign-name { font-size: 9px; font-weight: bold; margin-top: 3px; }
        .sign-title { color: #64748b; font-size: 8px; margin-top: 1px; }
        .scan-hint { color: #64748b; font-size: 7px; margin-top: 3px; }
        .draft {
            background: #fef3c7;
            color: #92400e;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
            padding: 4px;
            text-align: center;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @if($period->status->value === 'draft')
        <div class="draft">{{ __('pages.payroll_slip.draft_watermark') }}</div>
    @endif

    <table class="header">
        <tr>
            <td class="brand">
                @if($logo_src)
                    <img src="{{ $logo_src }}" alt="{{ $app_name }}" class="logo">
                @endif
                <div class="org">{{ $app_name }}</div>
                <div class="title">{{ __('pages.payroll_slip.title') }}</div>
                <div class="period">{{ $period->name }}</div>
            </td>
            <td class="meta">
                <span class="meta-label">{{ __('pages.payroll_slip.number') }}</span>
                <span class="meta-value">{{ $verification_code }}</span>
                <span class="meta-label" style="margin-top:6px;">{{ __('pages.payroll_slip.issued_at') }}</span>
                <span class="meta-value">{{ $issued_at->locale(app()->getLocale())->translatedFormat('d M Y H:i') }} WIB</span>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">{{ __('pages.payroll_slip.employee_section') }}</div>
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-label">{{ __('app.employee') }}</div>
                    <div class="info-value">{{ $employee->name }}</div>
                </td>
                <td>
                    <div class="info-label">{{ __('pages.payroll_slip.employee_number') }}</div>
                    <div class="info-value">{{ $employee->employee_number }}</div>
                </td>
                <td>
                    <div class="info-label">{{ __('app.branch') }}</div>
                    <div class="info-value">{{ $employee->branch?->name ?? '—' }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="info-label">{{ __('pages.payroll_slip.department') }}</div>
                    <div class="info-value">{{ $employee->department?->name ?? '—' }}</div>
                </td>
                <td colspan="2">
                    <div class="info-label">{{ __('pages.payroll_slip.position') }}</div>
                    <div class="info-value">{{ $employee->position?->name ?? '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table style="width:100%; margin-top:8px;">
        <tr>
            <td style="width:49%; vertical-align:top; padding-right:6px;">
                <div class="section-title">{{ __('pages.payroll_slip.earnings_section') }}</div>
                <table class="data-table">
                    <tr><td>{{ __('pages.payroll_slip.base_salary') }}</td><td class="amount">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</td></tr>
                    <tr><td>{{ __('pages.payroll_slip.allowances') }}</td><td class="amount">Rp {{ number_format($item->allowances, 0, ',', '.') }}</td></tr>
                    <tr class="subtotal"><td>{{ __('pages.payroll_slip.gross') }}</td><td class="amount">Rp {{ number_format($gross, 0, ',', '.') }}</td></tr>
                </table>
            </td>
            <td style="width:49%; vertical-align:top; padding-left:6px;">
                <div class="section-title">{{ __('pages.payroll_slip.deductions_section') }}</div>
                <table class="data-table">
                    @if($breakdown['attendance'] > 0)
                        <tr><td>{{ __('pages.payroll_slip.deduction_attendance', ['count' => $deductible_count]) }}</td><td class="amount deduction">- Rp {{ number_format($breakdown['attendance'], 0, ',', '.') }}</td></tr>
                    @endif
                    @if($breakdown['pph21'] > 0)
                        <tr><td>{{ __('pages.payroll_slip.deduction_pph21') }}</td><td class="amount deduction">- Rp {{ number_format($breakdown['pph21'], 0, ',', '.') }}</td></tr>
                    @endif
                    @if($breakdown['bpjs_kes_employee'] > 0)
                        <tr><td>{{ __('pages.payroll_slip.deduction_bpjs_kes') }}</td><td class="amount deduction">- Rp {{ number_format($breakdown['bpjs_kes_employee'], 0, ',', '.') }}</td></tr>
                    @endif
                    @if($breakdown['bpjs_tk_employee'] > 0)
                        <tr><td>{{ __('pages.payroll_slip.deduction_bpjs_tk') }}</td><td class="amount deduction">- Rp {{ number_format($breakdown['bpjs_tk_employee'], 0, ',', '.') }}</td></tr>
                    @endif
                    @if($item->deductions <= 0)
                        <tr><td colspan="2" style="color:#64748b; font-style:italic; text-align:center;">{{ __('pages.payroll_slip.no_deductions') }}</td></tr>
                    @endif
                    <tr class="subtotal"><td>{{ __('pages.payroll_slip.total_deductions') }}</td><td class="amount deduction">- Rp {{ number_format($item->deductions, 0, ',', '.') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="net">
        <tr>
            <td>{{ __('pages.payroll_slip.net_salary') }}</td>
            <td class="net-amount">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($item->notes)
        <p class="notes"><strong>{{ __('app.notes') }}:</strong> {{ $item->notes }}</p>
    @endif

    <div class="footer">
        <div class="footer-label">{{ __('pages.payroll_slip.hrd_signature') }}</div>
        @if($signature_src)
            <img src="{{ $signature_src }}" alt="{{ $hrd_name }}" class="signature-img">
        @endif
        <img src="{{ $qr_src }}" alt="{{ __('pages.payroll_slip.barcode_label') }}" class="qr" width="100" height="100">
        <div class="slip-code">{{ $verification_code }}</div>
        <div class="sign-name">{{ $hrd_name }}</div>
        <div class="sign-title">{{ $hrd_title }}</div>
    </div>
</body>
</html>
