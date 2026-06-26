@if($canEnrollFace ?? false)
    <section id="face-enrollment" class="profile-face-section panel">
        <div class="profile-face-section__head">
            <div>
                <h2 class="profile-face-section__title">{{ __('pages.profile.face_section') }}</h2>
                <p class="profile-face-section__subtitle">{{ __('pages.profile.face_section_hint') }}</p>
            </div>
            @if(($faceCount ?? 0) > 0)
                <span class="app-status-badge app-status-badge--active">{{ __('pages.profile.face_registered') }}</span>
            @else
                <span class="app-status-pending app-status-pending--compact">{{ __('pages.profile.face_not_registered') }}</span>
            @endif
        </div>

        @if($needsFaceEnrollment ?? false)
            <div class="app-notification-banner profile-face-section__banner leave-badge-pulse mb-4">
                <div class="app-notification-banner__body">
                    <span class="app-notification-banner__icon leave-badge-pulse">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </span>
                    <div>
                        <p class="app-notification-banner__title">{{ __('pages.profile.face_banner_title') }}</p>
                        <p class="app-notification-banner__message">{{ __('pages.profile.face_banner_message') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(($faceCount ?? 0) > 0)
            <div class="profile-face-section__registered mb-4">
                <p class="profile-face-section__registered-label">{{ __('pages.profile.face_registered_count', ['count' => $faceCount]) }}</p>
                @php
                    $primaryFace = $employee->faces->firstWhere('is_primary', true) ?? $employee->faces->first();
                @endphp
                @if($primaryFace?->hasPhoto())
                    <img src="{{ $primaryFace->photo_url }}" alt="" class="profile-face-section__preview">
                @endif
            </div>
            <p class="profile-face-section__rescan app-muted-text mb-3">{{ __('pages.profile.face_rescan_hint') }}</p>
        @endif

        <form id="profile-face-form" method="POST" action="{{ route('profile.face.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="face_descriptor" id="profile-face-descriptor">
            <input type="file" name="photo" id="profile-face-photo" class="hidden">
            <input type="hidden" name="is_primary" value="1">

            <div id="profile-face-camera-wrap" class="attendance-scan-camera">
                <video id="profile-face-video" autoplay muted playsinline class="attendance-scan-camera__video"></video>
                <canvas id="profile-face-canvas" class="hidden"></canvas>
                @include('partials.face-id-guide')
            </div>
            <p id="profile-face-status" class="attendance-scan-status">{{ __('pages.profile.face_loading') }}</p>
        </form>
    </section>

    @push('head')
        <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    @endpush

    @push('scripts')
        @vite('resources/js/face-scanner.js')
        <script>
            window.faceScannerConfig = {
                videoId: 'profile-face-video',
                canvasId: 'profile-face-canvas',
                cameraId: 'profile-face-camera-wrap',
                statusId: 'profile-face-status',
                descriptorInputId: 'profile-face-descriptor',
                photoInputId: 'profile-face-photo',
                formId: 'profile-face-form',
                knownFaces: [],
                stableFramesRequired: 2,
                scanIntervalMs: 450,
                requireGps: false,
                autoScan: true,
                poseGuide: 'enroll',
            };
        </script>
    @endpush
@endif
