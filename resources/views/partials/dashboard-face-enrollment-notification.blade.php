@php
    $showFaceEnrollment = $needsFaceEnrollment ?? app(\App\Services\ProfileFaceService::class)->needsEnrollment(auth()->user());
@endphp

@if($showFaceEnrollment)
    <div class="panel dashboard-notif-card dashboard-notif-card--face overflow-hidden app-notification-panel--active leave-badge-pulse mb-6">
        <div class="dashboard-notif-card__head">
            <div class="dashboard-notif-card__main">
                <span class="dashboard-notif-card__icon leave-badge-pulse">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <span class="dashboard-notif-card__count">!</span>
                </span>
                <div class="dashboard-notif-card__body">
                    <h2 class="dashboard-notif-card__title">{{ __('pages.dashboard.face_enrollment_title') }}</h2>
                    <p class="dashboard-notif-card__subtitle">{{ __('pages.dashboard.face_enrollment_message') }}</p>
                    <div class="dashboard-notif-card__chips">
                        <span class="dashboard-notif-chip dashboard-notif-chip--leave">{{ __('pages.profile.face_not_registered') }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}#face-enrollment" class="btn-primary dashboard-notif-card__btn shrink-0">
                {{ __('pages.dashboard.face_enrollment_action') }}
            </a>
        </div>
    </div>
@endif
