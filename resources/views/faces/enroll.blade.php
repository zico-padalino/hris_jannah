@extends('layouts.app')

@section('title', 'Daftarkan Wajah')
@section('subtitle', $employee->name)

@section('content')
    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Scan Wajah Pegawai</h2>
            <div class="relative overflow-hidden rounded-xl bg-slate-900">
                <video id="face-video" autoplay muted playsinline class="h-72 w-full object-cover"></video>
                <canvas id="face-canvas" class="hidden"></canvas>
            </div>
            <p id="face-status" class="mt-3 text-sm text-slate-500">Memuat model face recognition...</p>
            <button type="button" id="btn-capture-face" disabled class="mt-4 w-full rounded-lg bg-teal-700 px-4 py-3 text-sm font-medium text-white hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-50">
                Capture & Daftarkan Wajah
            </button>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Informasi</h2>
            <dl class="mb-6 space-y-2 text-sm">
                <div><dt class="text-slate-500">Pegawai</dt><dd>{{ $employee->name }}</dd></div>
                <div><dt class="text-slate-500">Cabang</dt><dd>{{ $employee->branch->name }}</dd></div>
                <div><dt class="text-slate-500">Wajah terdaftar</dt><dd>{{ $employee->faces->count() }}</dd></div>
            </dl>

            <form id="enroll-form" method="POST" action="{{ route('faces.store', $employee) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="face_descriptor" id="face-descriptor">
                <input type="file" name="photo" id="photo-input" class="hidden">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_primary" value="1" checked class="rounded border-slate-300">
                    Jadikan wajah utama
                </label>
                <p class="text-xs text-slate-500">Tekan tombol capture setelah wajah terdeteksi di kamera.</p>
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
