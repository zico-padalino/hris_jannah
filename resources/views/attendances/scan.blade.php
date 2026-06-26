@extends('layouts.app')

@section('title', 'Scan Absensi')
@section('subtitle', 'Verifikasi wajah dan lokasi GPS')

@section('content')
    @php
        $photoEnabled = $methods['photo'] ?? false;
        $gpsEnabled = $methods['gps'] ?? false;
        $defaultMethod = $photoEnabled ? 'photo' : ($gpsEnabled ? 'gps' : null);
        $shiftBlocksAttendance = $shiftBlocksAttendance ?? false;
    @endphp

    @if(! $photoEnabled && ! $gpsEnabled)
        <div class="app-notice">
            <p>
                <strong>{{ __('attendance.web_disabled_lead') }}</strong>
                {{ __('attendance.web_disabled_body') }}
                <a href="{{ route('settings.index') }}" class="attendance-scan-tips__link">{{ __('pages.settings.title') }}</a>.
            </p>
            @if($methods['fingerprint'] ?? false)
                <p class="mt-3">
                    <strong>{{ __('attendance.fingerprint_notice_lead') }}</strong>
                    {{ __('attendance.fingerprint_notice_body') }}
                </p>
            @endif
        </div>
    @else
        <div id="insecure-context-warning" class="app-notice app-notice--error mb-6 hidden">
            <strong>Kamera & GPS tidak tersedia via HTTP.</strong>
            Browser memblokir kamera saat akses <code>http://192.168.x.x</code>.
            <ol class="mt-2 list-decimal space-y-1 pl-5">
                <li>Pastikan server Laravel berjalan: <code>php artisan serve --host=0.0.0.0 --port=8000</code></li>
                <li>Jalankan proxy HTTPS: <code>npm run serve:https</code></li>
                <li>Buka <strong><code id="https-url">https://{{ request()->getHost() }}:8443</code></strong> (bukan port 8000)</li>
                <li>Terima peringatan sertifikat browser, lalu izinkan kamera & lokasi</li>
            </ol>
            <p class="mt-2 text-xs">Alternatif: buka <code>http://localhost:8000</code> langsung di komputer server.</p>
        </div>

        @if($shiftBlocksAttendance)
            <div class="app-notice app-notice--error mb-6">
                <p>
                    <strong>{{ __('attendance.no_shift_assigned') }}</strong>
                    {{ __('attendance.no_shift_assigned_hint') }}
                </p>
            </div>
        @endif

        @if($photoEnabled && $gpsEnabled)
            <div class="attendance-scan-tabs" role="tablist" aria-label="Metode absensi">
                <button
                    type="button"
                    role="tab"
                    data-scan-method="photo"
                    class="scan-method-tab {{ $defaultMethod === 'photo' ? 'scan-method-tab--active' : '' }}"
                    aria-selected="{{ $defaultMethod === 'photo' ? 'true' : 'false' }}"
                >
                    Scan Foto & Wajah
                </button>
                <button
                    type="button"
                    role="tab"
                    data-scan-method="gps"
                    class="scan-method-tab {{ $defaultMethod === 'gps' ? 'scan-method-tab--active' : '' }}"
                    aria-selected="{{ $defaultMethod === 'gps' ? 'true' : 'false' }}"
                >
                    GPS Lokasi (Cadangan)
                </button>
            </div>
        @endif

        <div class="attendance-scan-grid">
            @if($photoEnabled)
                <div id="panel-photo" class="panel attendance-scan-panel {{ $defaultMethod === 'gps' ? 'hidden' : '' }}">
                    <h2 class="attendance-scan-panel__title">Kamera Wajah</h2>
                    <div id="face-camera-wrap" class="attendance-scan-camera">
                        <video id="face-video" autoplay muted playsinline class="attendance-scan-camera__video"></video>
                        <canvas id="face-canvas" class="hidden"></canvas>
                        @include('partials.face-id-guide')
                    </div>
                    <p id="face-status" class="attendance-scan-status">Memuat model face recognition...</p>
                </div>
            @endif

            <div id="panel-gps-info" class="panel attendance-scan-panel {{ $defaultMethod === 'photo' ? 'hidden xl:col-span-1' : ($photoEnabled ? '' : 'xl:col-span-2') }}">
                @if($gpsEnabled && $defaultMethod === 'gps' && ! $photoEnabled)
                    <h2 class="attendance-scan-panel__title">Absensi GPS Lokasi</h2>
                    <p class="attendance-scan-panel__lead">
                        Mode cadangan tanpa verifikasi wajah. Pastikan Anda berada dalam radius lokasi cabang yang diizinkan.
                    </p>
                @elseif($gpsEnabled)
                    <div id="gps-only-heading" class="{{ $defaultMethod === 'photo' ? 'hidden' : '' }}">
                        <h2 class="attendance-scan-panel__title">Absensi GPS Lokasi</h2>
                        <p class="attendance-scan-panel__lead">Gunakan saat mesin fingerprint rusak — cukup latitude & longitude.</p>
                    </div>
                @endif

                <div class="attendance-scan-panel attendance-scan-panel--nested">
                    <h2 class="attendance-scan-panel__title {{ $gpsEnabled && $photoEnabled ? 'hidden' : '' }}" id="form-title-default">Form Absensi</h2>
                    <h2 class="attendance-scan-panel__title hidden" id="form-title-photo">Form Absensi</h2>

                    <form id="attendance-form" method="POST" action="{{ route('attendance.scan.store') }}" enctype="multipart/form-data" class="attendance-scan-form" @if($shiftBlocksAttendance) data-shift-blocked="1" @endif>
                        @csrf
                        <input type="hidden" name="method" id="attendance-method" value="{{ $defaultMethod }}">
                        <input type="hidden" name="face_descriptor" id="face-descriptor">
                        <input type="file" name="photo" id="photo-input" class="hidden">

                        <div class="attendance-scan-info">
                            <strong>Absensi otomatis:</strong> ikuti panduan animasi (tengah → kiri → kanan → tengah). Jika wajah cocok, absensi langsung diproses.
                        </div>

                        @if($gpsEnabled && ! $isEmployeeAccount)
                            <div id="employee-select-wrap" class="{{ $defaultMethod === 'photo' ? 'hidden' : '' }}">
                                <label class="form-label">Pegawai</label>
                                <select name="employee_id" id="employee-select" class="w-full">
                                    <option value="">— Pilih pegawai —</option>
                                    @foreach($employeesForGps as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($branches->count() > 1)
                            <div>
                                <label class="form-label">Cabang</label>
                                <select name="branch_id" id="branch-select" class="w-full">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif($branches->count() === 1)
                            <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
                            <p class="attendance-scan-branch-pill">Cabang: <strong>{{ $branches->first()->name }}</strong></p>
                        @endif

                        <div class="attendance-scan-coords">
                            <div>
                                <label class="form-label">Latitude</label>
                                <input name="latitude" id="latitude" step="any" required readonly class="w-full">
                            </div>
                            <div>
                                <label class="form-label">Longitude</label>
                                <input name="longitude" id="longitude" step="any" required readonly class="w-full">
                            </div>
                        </div>

                        <button type="button" id="btn-get-location" class="btn-secondary">
                            Ambil Lokasi GPS
                        </button>

                        <div id="location-info" class="attendance-scan-location-box">
                            Lokasi belum diambil. Izinkan akses GPS browser.
                        </div>

                        <div id="branch-locations" class="attendance-scan-locations"></div>

                        @if($gpsEnabled)
                            <button type="submit" id="btn-submit-gps" class="btn-primary {{ $defaultMethod === 'photo' ? 'hidden' : '' }}" @if($shiftBlocksAttendance) disabled @endif>
                                Absen dengan GPS Lokasi
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="app-notice attendance-scan-tips">
            <strong>Tips:</strong>
            @if($photoEnabled)
                Untuk absensi foto, daftarkan wajah pegawai di menu Pegawai → Daftarkan Wajah.
            @endif
            @if($gpsEnabled)
                Untuk absensi GPS, pastikan koordinat berada dalam radius lokasi cabang.
            @endif
        </div>
    @endif
@endsection

@if($photoEnabled && ($methods['photo'] ?? false))
    @push('head')
        <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    @endpush

    @push('scripts')
        @vite('resources/js/face-scanner.js')
    @endpush
@endif

@push('scripts')
    <script>
        const branches = @json($branchesForJs);
        const defaultBranchId = @json($defaultBranchId);
        const shiftBlocksAttendance = @json($shiftBlocksAttendance ?? false);
        const photoEnabled = @json($photoEnabled);
        const gpsEnabled = @json($gpsEnabled);

        function renderLocations() {
            const select = document.getElementById('branch-select');
            const container = document.getElementById('branch-locations');
            const branchId = select ? select.value : defaultBranchId;
            const branch = branches.find(b => String(b.id) === String(branchId));
            if (!branch || !container) return;
            container.innerHTML = '<p class="attendance-scan-locations__label">Lokasi absensi yang diizinkan:</p>' +
                (branch.locations.length
                    ? branch.locations.map(l => `<div class="attendance-scan-location-item">${l.name} — radius ${l.radius_meters}m (${l.latitude}, ${l.longitude})</div>`).join('')
                    : '<p class="attendance-scan-location-item">Tidak ada lokasi aktif.</p>');
        }

        document.getElementById('branch-select')?.addEventListener('change', renderLocations);
        renderLocations();

        function setLocationInfo(message, success = false) {
            const box = document.getElementById('location-info');
            if (!box) return;
            box.textContent = message;
            box.classList.toggle('attendance-scan-location-box--success', success);
        }

        function fetchLocation() {
            if (!window.isSecureContext) {
                setLocationInfo('GPS memerlukan HTTPS. Lihat petunjuk di atas.');
                return;
            }
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition((pos) => {
                document.getElementById('latitude').value = pos.coords.latitude.toFixed(7);
                document.getElementById('longitude').value = pos.coords.longitude.toFixed(7);
                setLocationInfo(`Lokasi: ${pos.coords.latitude.toFixed(7)}, ${pos.coords.longitude.toFixed(7)} (akurasi ~${Math.round(pos.coords.accuracy)}m)`, true);
            }, () => {
                setLocationInfo('Gagal mengambil lokasi. Izinkan akses GPS.');
            }, { enableHighAccuracy: true });
        }

        if (!window.isSecureContext) {
            document.getElementById('insecure-context-warning')?.classList.remove('hidden');
        }

        fetchLocation();
        document.getElementById('btn-get-location')?.addEventListener('click', fetchLocation);

        const attendanceForm = document.getElementById('attendance-form');
        if (shiftBlocksAttendance && attendanceForm) {
            attendanceForm.addEventListener('submit', (event) => {
                event.preventDefault();
            });
        }

        function setScanMethod(method) {
            const methodInput = document.getElementById('attendance-method');
            if (methodInput) methodInput.value = method;

            document.querySelectorAll('.scan-method-tab').forEach((btn) => {
                const active = btn.dataset.scanMethod === method;
                btn.classList.toggle('scan-method-tab--active', active);
                btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            document.getElementById('panel-photo')?.classList.toggle('hidden', method !== 'photo');
            document.getElementById('gps-only-heading')?.classList.toggle('hidden', method !== 'gps');
            document.getElementById('employee-select-wrap')?.classList.toggle('hidden', method !== 'gps');
            document.getElementById('btn-submit-gps')?.classList.toggle('hidden', method !== 'gps');

            if (method === 'photo' && ! shiftBlocksAttendance) {
                window.dispatchEvent(new CustomEvent('face-scanner:resume'));
            } else {
                window.dispatchEvent(new CustomEvent('face-scanner:pause'));
            }

            const employeeSelect = document.getElementById('employee-select');
            if (employeeSelect) {
                employeeSelect.required = method === 'gps';
            }
        }

        document.querySelectorAll('.scan-method-tab').forEach((btn) => {
            btn.addEventListener('click', () => setScanMethod(btn.dataset.scanMethod));
        });

        if (photoEnabled) {
            window.faceScannerConfig = {
                videoId: 'face-video',
                canvasId: 'face-canvas',
                cameraId: 'face-camera-wrap',
                statusId: 'face-status',
                descriptorInputId: 'face-descriptor',
                photoInputId: 'photo-input',
                formId: 'attendance-form',
                methodInputId: 'attendance-method',
                methodValue: 'photo',
                branchSelectId: 'branch-select',
                latitudeInputId: 'latitude',
                longitudeInputId: 'longitude',
                defaultBranchId: defaultBranchId,
                knownFaces: @json($facesForJs ?? []),
                matchThreshold: @json($faceMatchThreshold ?? 0.6),
                stableFramesRequired: 2,
                scanIntervalMs: 450,
                autoScan: true,
                poseGuide: 'attendance',
                startPaused: {{ ($defaultMethod === 'gps' || ($shiftBlocksAttendance ?? false)) ? 'true' : 'false' }},
                attendanceBlocked: @json($shiftBlocksAttendance ?? false),
                blockedMessage: @json(__('attendance.no_shift_assigned')),
            };
        }

        if (shiftBlocksAttendance) {
            window.dispatchEvent(new CustomEvent('face-scanner:pause'));
        }
    </script>
@endpush
