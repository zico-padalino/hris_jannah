@if($leave->hasProof())
    @once
        @push('modals')
            @include('partials.leave-proof-modal')
        @endpush
    @endonce

    <button
        type="button"
        class="leave-proof-link"
        data-leave-proof-trigger
        data-proof-url="{{ $leave->proof_url }}"
        data-proof-kind="{{ $leave->proofIsPdf() ? 'pdf' : 'image' }}"
        data-proof-title="{{ $leave->employee?->name ?? __('app.proof') }}"
        data-proof-meta="{{ $leave->type->label() }} · {{ $leave->start_date->format('d/m/Y') }}"
    >
        {{ __('leave.view_proof') }}
    </button>
@else
    <span class="text-slate-400">—</span>
@endif
