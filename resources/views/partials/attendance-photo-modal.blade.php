<div
    id="attendance-photo-modal"
    class="attendance-photo-modal hidden"
    role="dialog"
    aria-modal="true"
    aria-labelledby="attendance-photo-title"
    aria-hidden="true"
>
    <div class="attendance-photo-modal__backdrop" data-attendance-photo-close></div>
    <div class="attendance-photo-modal__dialog">
        <div class="attendance-photo-modal__head">
            <div>
                <h3 id="attendance-photo-title" class="attendance-photo-modal__title">{{ __('attendance.photo_modal_title') }}</h3>
                <p id="attendance-photo-meta" class="attendance-photo-modal__meta"></p>
            </div>
            <button type="button" class="attendance-photo-modal__close" data-attendance-photo-close aria-label="{{ __('attendance.photo_close') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="attendance-photo-modal__body">
            <img id="attendance-photo-image" src="" alt="{{ __('attendance.photo_modal_title') }}" class="attendance-photo-modal__image hidden">
        </div>
        <div class="attendance-photo-modal__foot">
            <a id="attendance-photo-download" href="#" download class="btn-secondary attendance-photo-modal__download">{{ __('attendance.photo_download') }}</a>
            <button type="button" class="btn-primary" data-attendance-photo-close>{{ __('attendance.photo_close') }}</button>
        </div>
    </div>
</div>
