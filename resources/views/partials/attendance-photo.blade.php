@php
    $size = $size ?? 'sm';
@endphp

@if($attendance->hasPhoto())
    <button
        type="button"
        class="attendance-photo-trigger attendance-photo-trigger--{{ $size }}"
        data-attendance-photo-trigger
        data-photo-url="{{ $attendance->photo_url }}"
        data-photo-title="{{ __('attendance.photo_modal_title') }} — {{ $attendance->employee->name }}"
        data-photo-meta="{{ $attendance->attended_at->format('d/m/Y H:i') }} {{ __('app.wib') }} · {{ $attendance->type->label() }}"
        title="{{ __('attendance.photo_view') }}"
        aria-label="{{ __('attendance.photo_view') }}"
    >
        <img
            src="{{ $attendance->photo_url }}"
            alt="{{ __('attendance.photo_alt', ['name' => $attendance->employee->name]) }}"
            class="attendance-photo-trigger__image"
            loading="lazy"
        >
        <span class="attendance-photo-trigger__overlay" aria-hidden="true">
            <span class="attendance-photo-trigger__overlay-text">{{ __('attendance.photo_view') }}</span>
        </span>
    </button>
@else
    <span class="attendance-photo-placeholder attendance-photo-placeholder--{{ $size }}" title="{{ __('attendance.photo_none') }}">
        <svg class="attendance-photo-placeholder__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
        </svg>
    </span>
@endif
