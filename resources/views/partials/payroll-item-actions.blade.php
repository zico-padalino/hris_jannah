@props([
    'payroll',
    'item',
])

@php
    $signature = $item->slipSignature;
    $signaturePending = $signature?->status === \App\Enums\PayrollSlipSignatureStatus::Pending;
    $signatureApproved = $signature?->status === \App\Enums\PayrollSlipSignatureStatus::Approved;
@endphp

<div class="payroll-item-actions">
    <a
        href="{{ route('payrolls.items.slip', [$payroll, $item]) }}"
        class="payroll-item-actions__btn payroll-item-actions__btn--slip"
    >
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
        {{ __('pages.payroll_slip.view') }}
    </a>

    @perm('payroll.manage')
        @if($signaturePending)
            <form method="POST" action="{{ route('payrolls.items.approve-signature', [$payroll, $item]) }}" class="payroll-item-actions__form">
                @csrf
                <button type="submit" class="payroll-item-actions__btn payroll-item-actions__btn--approve">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('pages.payroll_slip.approve_signature') }}
                </button>
            </form>
        @elseif($signatureApproved)
            <span class="payroll-item-actions__badge payroll-item-actions__badge--signed">{{ __('pages.payroll_slip.signature_signed') }}</span>
        @endif
    @else
        @if($signaturePending)
            <span class="payroll-item-actions__badge payroll-item-actions__badge--pending">{{ __('pages.payroll_slip.signature_pending') }}</span>
        @elseif($signatureApproved)
            <span class="payroll-item-actions__badge payroll-item-actions__badge--signed">{{ __('pages.payroll_slip.signature_signed') }}</span>
        @endif
    @endperm

    @if($item->deductions > 0)
        <a
            href="{{ route('payrolls.items.deductions', [$payroll, $item]) }}"
            class="payroll-item-actions__btn payroll-item-actions__btn--deduction"
        >
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.9.693 2.166 1.638m-5.8 0A2.251 2.251 0 0013.5 4.5h1.086a48.424 48.424 0 010 1.066m-7.5 0h7.5" />
            </svg>
            {{ __('pages.payroll_slip.deduction_detail') }}
        </a>
    @endif
</div>
