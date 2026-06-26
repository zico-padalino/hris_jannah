@extends('layouts.slip')

@section('toolbar-extra')
    @if($can_request_signature)
        <form method="POST" action="{{ route('payrolls.items.request-signature', [$period, $item]) }}" class="payslip-toolbar__form">
            @csrf
            <button type="submit" class="payroll-deduction-back payslip-toolbar__btn payslip-toolbar__btn--sign">
                <span class="payroll-deduction-back__icon" aria-hidden="true">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </span>
                <span class="payslip-toolbar__btn-label">{{ __('pages.payroll_slip.request_signature') }}</span>
            </button>
        </form>
    @elseif($signature_pending)
        <span class="payslip-toolbar__status payslip-toolbar__status--pending">{{ __('pages.payroll_slip.signature_pending') }}</span>
    @elseif($signature_approved)
        <span class="payslip-toolbar__status payslip-toolbar__status--approved">{{ __('pages.payroll_slip.signature_signed') }}</span>
    @endif

    @if($signature_approved)
        <a href="{{ route('payrolls.items.slip.download', [$period, $item]) }}" class="payroll-deduction-back payslip-toolbar__btn payslip-toolbar__btn--download">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M7.5 11.25L12 15.75m0 0l4.5-4.5M12 15.75V3.75" />
                </svg>
            </span>
            <span class="payslip-toolbar__btn-label">{{ __('pages.payroll_slip.download') }}</span>
        </a>
    @endif
@endsection

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
                <p><span>{{ __('pages.payroll_slip.issued_at') }}</span><strong>{{ $issued_at->locale(app()->getLocale())->translatedFormat('d M Y H:i') }} WIB</strong></p>
            </div>
        </header>

        <section class="payslip-section payslip-section--compact">
            <h2 class="payslip-section__title">{{ __('pages.payroll_slip.employee_section') }}</h2>
            <dl class="payslip-info-grid payslip-info-grid--compact">
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
                <div class="payslip-info-grid__span">
                    <dt>{{ __('pages.payroll_slip.position') }}</dt>
                    <dd>{{ $employee->position?->name ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <div class="payslip-tables">
            <section class="payslip-section payslip-section--compact">
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

            <section class="payslip-section payslip-section--compact">
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
        </div>

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

                @if($signature_approved)
                    @if($has_hrd_signature && $hrd_signature_url)
                        <img
                            src="{{ $hrd_signature_url }}"
                            alt="{{ $hrd_name }}"
                            class="payslip-footer__sign-image"
                        >
                    @else
                        <div class="payslip-footer__sign-placeholder" aria-hidden="true"></div>
                    @endif

                    <div class="payslip-qr-wrap">
                        <div id="payslip-qr" class="payslip-qr" aria-label="{{ __('pages.payroll_slip.barcode_label') }}"></div>
                    </div>
                    <p class="payslip-barcode__code">{{ $verification_code }}</p>
                    <p class="payslip-footer__sign-name">{{ $hrd_name }}</p>
                    <p class="payslip-footer__sign-title">{{ $hrd_title }}</p>
                    <p class="payslip-footer__scan-hint">{{ __('pages.payroll_slip.scan_hint') }}</p>
                @elseif($signature_pending)
                    <p class="payslip-footer__pending">{{ __('pages.payroll_slip.signature_waiting') }}</p>
                    <p class="payslip-footer__sign-name">{{ $hrd_name }}</p>
                    <p class="payslip-footer__sign-title">{{ $hrd_title }}</p>
                @else
                    <p class="payslip-footer__unsigned">{{ __('pages.payroll_slip.signature_unsigned') }}</p>
                    <p class="payslip-footer__sign-name">{{ $hrd_name }}</p>
                    <p class="payslip-footer__sign-title">{{ $hrd_title }}</p>
                @endif
            </div>
        </footer>
    </article>
@endsection

@if($signature_approved)
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
                    width: 88,
                    height: 88,
                    colorDark: '#1e293b',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H,
                    quietZone: 6,
                    quietZoneColor: '#ffffff',
                    dotScale: 0.85,
                };

                @if($appBranding->hasLogo())
                    options.logo = @json(url($appBranding->logoUrl()));
                    options.logoWidth = 20;
                    options.logoHeight = 20;
                    options.logoBackgroundTransparent = true;
                    options.crossOrigin = 'anonymous';
                @endif

                new QRCode(target, options);
            })();
        </script>
    @endpush
@endif
