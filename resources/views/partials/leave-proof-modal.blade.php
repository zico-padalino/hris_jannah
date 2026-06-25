<div
    id="leave-proof-modal"
    class="leave-proof-modal hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="leave-proof-title"
    aria-hidden="true"
>
    <div class="leave-proof-modal__backdrop" data-leave-proof-close></div>
    <div class="leave-proof-modal__dialog">
        <div class="leave-proof-modal__head">
            <div class="min-w-0">
                <h3 id="leave-proof-title" class="leave-proof-modal__title">{{ __('leave.proof_modal_title') }}</h3>
                <p id="leave-proof-meta" class="leave-proof-modal__meta"></p>
            </div>
            <button type="button" class="leave-proof-modal__close" data-leave-proof-close aria-label="{{ __('leave.proof_close') }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="leave-proof-modal__body">
            <img id="leave-proof-image" src="" alt="{{ __('leave.proof_modal_title') }}" class="leave-proof-modal__image hidden">
            <iframe id="leave-proof-pdf" title="{{ __('leave.proof_modal_title') }}" class="leave-proof-modal__pdf hidden"></iframe>
        </div>
        <div class="leave-proof-modal__foot">
            <a id="leave-proof-download" href="#" download class="btn-secondary leave-proof-modal__download">{{ __('leave.proof_download') }}</a>
            <button type="button" class="btn-primary" data-leave-proof-close>{{ __('leave.proof_close') }}</button>
        </div>
    </div>
</div>
