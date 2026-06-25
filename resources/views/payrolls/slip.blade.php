@extends('layouts.slip')

@section('content')
    <article class="payslip-sheet" id="payslip-sheet">
        @if($period->status->value === 'draft')
            <div class="payslip-draft-badge">{{ __('pages.payroll_slip.draft_watermark') }}</div>
        @endif

        <header class="payslip-header">
            <div class="payslip-header__brand">
                @if($appBranding->hasLogo())
                    <img src="{{ $appBranding->logoUrl() }}" alt="{{ $appBranding->name() }}" class="payslip-header__logo">
                @endif
                <div>
                    <p class="payslip-header__org">{{ $appBranding->name() }}</p>
                    <h1 class="payslip-header__title">{{ __('pages.payroll_slip.title') }}</h1>
                    <p class="payslip-header__period">{{ $period->name }}</p>
                </div>
            </div>
            <div class="payslip-header__meta">
                <p><span>{{ __('pages.payroll_slip.number') }}</span><strong>{{ $verification_code }}</strong></p>
                <p><span>{{ __('pages.payroll_slip.issued_at') }}</span><strong>{{ $issued_at->locale(app()->getLocale())->translatedFormat('d F Y H:i') }} WIB</strong></p>
            </div>
        </header>

        <section class="payslip-section">
            <h2 class="payslip-section__title">{{ __('pages.payroll_slip.employee_section') }}</h2>
            <dl class="payslip-info-grid">
                <div>
                    <dt>{{ __('app.employee') }}</dt>
                    <dd>{{ $employee->name }}</dd>
                </div>
                <div>
                    <dt>{{ __('pages.payroll_slip.employee_number') }}</dt>
                    <dd>{{ $employee->employee_number }}</dd>
                </div>
                <div>
                    <dt>{{ __('app.branch') }}</dt>
                    <dd>{{ $employee->branch?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('pages.payroll_slip.department') }}</dt>
                    <dd>{{ $employee->department?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt>{{ __('pages.payroll_slip.position') }}</dt>
                    <dd>{{ $employee->position?->name ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="payslip-section">
            <h2 class="payslip-section__title">{{ __('pages.payroll_slip.earnings_section') }}</h2>
            <table class="payslip-table">
                <tbody>
                    <tr>
                        <td>{{ __('pages.payroll_slip.base_salary') }}</td>
                        <td class="payslip-table__amount">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('pages.payroll_slip.allowances') }}</td>
                        <td class="payslip-table__amount">Rp {{ number_format($item->allowances, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="payslip-table__subtotal">
                        <td>{{ __('pages.payroll_slip.gross') }}</td>
                        <td class="payslip-table__amount">Rp {{ number_format($gross, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="payslip-section">
            <h2 class="payslip-section__title">{{ __('pages.payroll_slip.deductions_section') }}</h2>
            <table class="payslip-table">
                <tbody>
                    @if($breakdown['attendance'] > 0)
                        <tr>
                            <td>{{ __('pages.payroll_slip.deduction_attendance', ['count' => $deductible_count]) }}</td>
                            <td class="payslip-table__amount payslip-table__amount--deduction">- Rp {{ number_format($breakdown['attendance'], 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($breakdown['pph21'] > 0)
                        <tr>
                            <td>{{ __('pages.payroll_slip.deduction_pph21') }}</td>
                            <td class="payslip-table__amount payslip-table__amount--deduction">- Rp {{ number_format($breakdown['pph21'], 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($breakdown['bpjs_kes_employee'] > 0)
                        <tr>
                            <td>{{ __('pages.payroll_slip.deduction_bpjs_kes') }}</td>
                            <td class="payslip-table__amount payslip-table__amount--deduction">- Rp {{ number_format($breakdown['bpjs_kes_employee'], 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($breakdown['bpjs_tk_employee'] > 0)
                        <tr>
                            <td>{{ __('pages.payroll_slip.deduction_bpjs_tk') }}</td>
                            <td class="payslip-table__amount payslip-table__amount--deduction">- Rp {{ number_format($breakdown['bpjs_tk_employee'], 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($item->deductions <= 0)
                        <tr>
                            <td colspan="2" class="payslip-table__empty">{{ __('pages.payroll_slip.no_deductions') }}</td>
                        </tr>
                    @endif
                    <tr class="payslip-table__subtotal">
                        <td>{{ __('pages.payroll_slip.total_deductions') }}</td>
                        <td class="payslip-table__amount payslip-table__amount--deduction">- Rp {{ number_format($item->deductions, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="payslip-net">
            <span>{{ __('pages.payroll_slip.net_salary') }}</span>
            <strong>Rp {{ number_format($item->net_salary, 0, ',', '.') }}</strong>
        </section>

        @if($item->notes)
            <p class="payslip-notes"><strong>{{ __('app.notes') }}:</strong> {{ $item->notes }}</p>
        @endif

        <footer class="payslip-footer">
            <div class="payslip-footer__signature">
                <p class="payslip-footer__sign-label">{{ __('pages.payroll_slip.hrd_signature') }}</p>
                <div class="payslip-qr-wrap">
                    <div id="payslip-qr" class="payslip-qr" aria-label="{{ __('pages.payroll_slip.barcode_label') }}"></div>
                </div>
                <p class="payslip-barcode__code">{{ $verification_code }}</p>
                <p class="payslip-footer__sign-name">{{ $hrd_name }}</p>
                <p class="payslip-footer__sign-title">{{ $hrd_title }}</p>
                <p class="payslip-footer__scan-hint">{{ __('pages.payroll_slip.scan_hint') }}</p>
            </div>
        </footer>
    </article>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/easyqrcodejs@4.6.1/dist/easy.qrcode.min.js"></script>
    <script>
        (function () {
            const target = document.getElementById('payslip-qr');

            if (!target || typeof QRCode === 'undefined') {
                return;
            }

            const options = {
                text: @json($scan_text),
                width: 128,
                height: 128,
                colorDark: '#1e293b',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H,
                quietZone: 8,
                quietZoneColor: '#ffffff',
                dotScale: 0.85,
            };

            @if($appBranding->hasLogo())
                options.logo = @json(url($appBranding->logoUrl()));
                options.logoWidth = 28;
                options.logoHeight = 28;
                options.logoBackgroundTransparent = true;
                options.crossOrigin = 'anonymous';
            @endif

            new QRCode(target, options);
        })();
    </script>
@endpush
