@extends('layouts.app')

@section('title', 'Scan Absensi')
@section('subtitle', 'Verifikasi wajah dan lokasi GPS')

@section('content')
    @php
        $photoEnabled = $methods['photo'] ?? false;
        $gpsEnabled = $methods['gps'] ?? false;
        $defaultMethod = $photoEnabled ? 'photo' : ($gpsEnabled ? 'gps' : null);
    @endphp

    @if(! $photoEnabled && ! $gpsEnabled)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm leading-relaxed text-amber-900 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-100">
            <p>
                <strong>{{ __('attendance.web_disabled_lead') }}</strong>
                {{ __('attendance.web_disabled_body') }}
                <a href="{{ route('settings.index') }}" class="font-medium text-teal-800 underline dark:text-teal-300">{{ __('pages.settings.title') }}</a>.
            </p>
            @if($methods['fingerprint'] ?? false)
                <p class="mt-3">
                    <strong>{{ __('attendance.fingerprint_notice_lead') }}</strong>
                    {{ __('attendance.fingerprint_notice_body') }}
                </p>
            @endif
        </div>
    @else
        <div id="insecure-context-warning" class="mb-6 hidden rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900">
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

        @if($photoEnabled && $gpsEnabled)
            <div class="mb-6 flex flex-wrap gap-2">
                <button type="button" data-scan-method="photo" class="scan-method-tab rounded-lg border-2 border-teal-600 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-900">
                    Scan Foto & Wajah
                </button>
                <button type="button" data-scan-method="gps" class="scan-method-tab rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    GPS Lokasi (Cadangan)
                </button>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-2">
            @if($photoEnabled)
                <div id="panel-photo" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm {{ $defaultMethod === 'gps' ? 'hidden' : '' }}">
                    <h2 class="mb-4 text-lg font-semibold">Kamera Wajah</h2>
                    <div class="relative overflow-hidden rounded-xl bg-slate-900">
                        <video id="face-video" autoplay muted playsinline class="h-72 w-full object-cover"></video>
                        <canvas id="face-canvas" class="hidden"></canvas>
                    </div>
                    <p id="face-status" class="mt-3 text-sm text-slate-500">Memuat model face recognition...</p>
                    <button type="button" id="btn-capture-face" disabled class="mt-4 w-full rounded-lg bg-teal-700 px-4 py-3 text-sm font-medium text-white hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-50">
                        Scan Wajah & Absen
                    </button>
                </div>
            @endif

            <div id="panel-gps-info" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm {{ $defaultMethod === 'photo' ? 'hidden xl:col-span-1' : ($photoEnabled ? '' : 'xl:col-span-2') }}">
                @if($gpsEnabled && $defaultMethod === 'gps' && ! $photoEnabled)
                    <h2 class="mb-4 text-lg font-semibold">Absensi GPS Lokasi</h2>
                    <p class="mb-4 text-sm text-slate-600">
                        Mode cadangan tanpa verifikasi wajah. Pastikan Anda berada dalam radius lokasi cabang yang diizinkan.
                    </p>
                @elseif($gpsEnabled)
                    <div id="gps-only-heading" class="mb-4 {{ $defaultMethod === 'photo' ? 'hidden' : '' }}">
                        <h2 class="text-lg font-semibold">Absensi GPS Lokasi</h2>
                        <p class="mt-1 text-sm text-slate-600">Gunakan saat mesin fingerprint rusak — cukup latitude & longitude.</p>
                    </div>
                @endif

                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm {{ $photoEnabled && $defaultMethod !== 'gps' ? 'border-0 p-0 shadow-none' : '' }}">
                    <h2 class="mb-4 text-lg font-semibold {{ $gpsEnabled && $photoEnabled ? 'hidden' : '' }}" id="form-title-default">Form Absensi</h2>
                    <h2 class="mb-4 hidden text-lg font-semibold" id="form-title-photo">Form Absensi</h2>

                    <form id="attendance-form" method="POST" action="{{ route('attendance.scan.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="method" id="attendance-method" value="{{ $defaultMethod }}">
                        <input type="hidden" name="face_descriptor" id="face-descriptor">
                        <input type="file" name="photo" id="photo-input" class="hidden">

                        <div class="rounded-lg border border-teal-200 bg-teal-50 px-3 py-2 text-sm text-teal-900">
                            <strong>Absensi otomatis:</strong> tap/scan pertama hari ini = <strong>Masuk</strong>, berikutnya = <strong>Pulang</strong>, bergantian seterusnya.
                        </div>

                        @if($gpsEnabled && ! $isEmployeeAccount)
                            <div id="employee-select-wrap" class="{{ $defaultMethod === 'photo' ? 'hidden' : '' }}">
                                <label class="mb-1 block text-sm font-medium">Pegawai</label>
                                <select name="employee_id" id="employee-select" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                                    <option value="">— Pilih pegawai —</option>
                                    @foreach($employeesForGps as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_number }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($branches->count() > 1)
                            <div>
                                <label class="mb-1 block text-sm font-medium">Cabang</label>
                                <select name="branch_id" id="branch-select" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @elseif($branches->count() === 1)
                            <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
                            <p class="rounded-lg bg-slate-50 px-3 py-2 text-sm">Cabang: <strong>{{ $branches->first()->name }}</strong></p>
                        @endif

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium">Latitude</label>
                                <input name="latitude" id="latitude" step="any" required readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium">Longitude</label>
                                <input name="longitude" id="longitude" step="any" required readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
                            </div>
                        </div>

                        <button type="button" id="btn-get-location" class="w-full rounded-lg border border-teal-300 bg-teal-50 px-4 py-2 text-sm text-teal-800 hover:bg-teal-100">
                            Ambil Lokasi GPS
                        </button>

                        <div id="location-info" class="rounded-lg bg-slate-50 p-3 text-xs text-slate-600">
                            Lokasi belum diambil. Izinkan akses GPS browser.
                        </div>

                        <div id="branch-locations" class="space-y-2 text-xs text-slate-600"></div>

                        @if($gpsEnabled)
                            <button type="submit" id="btn-submit-gps" class="w-full rounded-lg bg-amber-600 px-4 py-3 text-sm font-medium text-white hover:bg-amber-700 {{ $defaultMethod === 'photo' ? 'hidden' : '' }}">
                                Absen dengan GPS Lokasi
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
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
        const photoEnabled = @json($photoEnabled);
        const gpsEnabled = @json($gpsEnabled);

        function renderLocations() {
            const select = document.getElementById('branch-select');
            const container = document.getElementById('branch-locations');
            const branchId = select ? select.value : defaultBranchId;
            const branch = branches.find(b => String(b.id) === String(branchId));
            if (!branch || !container) return;
            container.innerHTML = '<p class="font-medium text-slate-700">Lokasi absensi yang diizinkan:</p>' +
                (branch.locations.length
                    ? branch.locations.map(l => `<div class="rounded border border-slate-200 p-2">${l.name} — radius ${l.radius_meters}m (${l.latitude}, ${l.longitude})</div>`).join('')
                    : '<p>Tidak ada lokasi aktif.</p>');
        }

        document.getElementById('branch-select')?.addEventListener('change', renderLocations);
        renderLocations();

        function fetchLocation() {
            if (!window.isSecureContext) {
                document.getElementById('location-info').textContent = 'GPS memerlukan HTTPS. Lihat petunjuk di atas.';
                return;
            }
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition((pos) => {
                document.getElementById('latitude').value = pos.coords.latitude.toFixed(7);
                document.getElementById('longitude').value = pos.coords.longitude.toFixed(7);
                document.getElementById('location-info').textContent = `Lokasi: ${pos.coords.latitude.toFixed(7)}, ${pos.coords.longitude.toFixed(7)} (akurasi ~${Math.round(pos.coords.accuracy)}m)`;
            }, () => {
                document.getElementById('location-info').textContent = 'Gagal mengambil lokasi. Izinkan akses GPS.';
            }, { enableHighAccuracy: true });
        }

        if (!window.isSecureContext) {
            document.getElementById('insecure-context-warning')?.classList.remove('hidden');
        }

        fetchLocation();
        document.getElementById('btn-get-location')?.addEventListener('click', fetchLocation);

        function setScanMethod(method) {
            const methodInput = document.getElementById('attendance-method');
            if (methodInput) methodInput.value = method;

            document.querySelectorAll('.scan-method-tab').forEach((btn) => {
                const active = btn.dataset.scanMethod === method;
                btn.classList.toggle('border-2', active);
                btn.classList.toggle('border-teal-600', active);
                btn.classList.toggle('bg-teal-50', active);
                btn.classList.toggle('font-semibold', active);
                btn.classList.toggle('text-teal-900', active);
                btn.classList.toggle('border', !active);
                btn.classList.toggle('border-slate-300', !active);
                btn.classList.toggle('bg-white', !active);
                btn.classList.toggle('font-medium', !active);
                btn.classList.toggle('text-slate-700', !active);
            });

            document.getElementById('panel-photo')?.classList.toggle('hidden', method !== 'photo');
            document.getElementById('gps-only-heading')?.classList.toggle('hidden', method !== 'gps');
            document.getElementById('employee-select-wrap')?.classList.toggle('hidden', method !== 'gps');
            document.getElementById('btn-submit-gps')?.classList.toggle('hidden', method !== 'gps');

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
                statusId: 'face-status',
                captureButtonId: 'btn-capture-face',
                descriptorInputId: 'face-descriptor',
                photoInputId: 'photo-input',
                formId: 'attendance-form',
                methodInputId: 'attendance-method',
                methodValue: 'photo',
            };
        }

        document.getElementById('attendance-form')?.addEventListener('submit', (event) => {
            const method = document.getElementById('attendance-method')?.value;
            if (method === 'gps') {
                return;
            }
            if (!document.getElementById('face-descriptor')?.value) {
                event.preventDefault();
                alert('Scan wajah terlebih dahulu.');
            }
        });
    </script>
@endpush
