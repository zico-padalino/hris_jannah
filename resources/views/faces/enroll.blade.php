@extends('layouts.app')

@section('title', 'Daftarkan Wajah')
@section('subtitle', $employee->name)

@section('content')
    <div class="attendance-enroll-grid">
        <div class="panel attendance-scan-panel">
            <h2 class="attendance-scan-panel__title">Scan Wajah Pegawai</h2>
            <div class="attendance-scan-camera">
                <video id="face-video" autoplay muted playsinline class="attendance-scan-camera__video"></video>
                <canvas id="face-canvas" class="hidden"></canvas>
            </div>
            <p id="face-status" class="attendance-scan-status">Memuat model face recognition...</p>
            <div class="attendance-scan-actions">
                <button type="button" id="btn-capture-face" disabled class="btn-primary">
                    Capture & Daftarkan Wajah
                </button>
            </div>
        </div>

        <div class="panel attendance-scan-panel">
            <h2 class="attendance-scan-panel__title">Informasi</h2>
            <dl class="attendance-enroll-dl">
                <div class="attendance-enroll-dl__row">
                    <dt>Pegawai</dt>
                    <dd>{{ $employee->name }}</dd>
                </div>
                <div class="attendance-enroll-dl__row">
                    <dt>Cabang</dt>
                    <dd>{{ $employee->branch->name }}</dd>
                </div>
                <div class="attendance-enroll-dl__row">
                    <dt>Wajah terdaftar</dt>
                    <dd>{{ $employee->faces->count() }}</dd>
                </div>
            </dl>

            <form id="enroll-form" method="POST" action="{{ route('faces.store', $employee) }}" enctype="multipart/form-data" class="attendance-scan-form">
                @csrf
                <input type="hidden" name="face_descriptor" id="face-descriptor">
                <input type="file" name="photo" id="photo-input" class="hidden">
                <label class="flex items-center gap-2 text-sm font-semibold" style="color: var(--app-text);">
                    <input type="checkbox" name="is_primary" value="1" checked class="rounded border-slate-300">
                    Jadikan wajah utama
                </label>
                <p class="attendance-enroll-note">Tekan tombol capture setelah wajah terdeteksi di kamera.</p>
            </form>
        </div>
    </div>
@endsection

@push('head')
    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
@endpush

@push('scripts')
    @vite('resources/js/face-scanner.js')
    <script>
        window.faceScannerConfig = {
            videoId: 'face-video',
            canvasId: 'face-canvas',
            statusId: 'face-status',
            captureButtonId: 'btn-capture-face',
            descriptorInputId: 'face-descriptor',
            photoInputId: 'photo-input',
            formId: 'enroll-form',
        };
    </script>
@endpush
