@extends('layouts.app')

@section('title', 'Tambah Lokasi Absensi')
@section('subtitle', $branch->name)

@section('content')
    <div class="attendance-location-page mx-auto max-w-5xl space-y-4">
        <a href="{{ route('branches.show', $branch) }}" class="btn-secondary inline-flex items-center gap-2">
            <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Kembali ke Detail Cabang
        </a>

        <div class="panel attendance-location-panel p-6 sm:p-8">
            <div class="attendance-location-panel__header">
                <h2 class="attendance-location-panel__title">Tambah Lokasi Absensi</h2>
                <p class="attendance-location-panel__desc">
                    Atur titik GPS dan radius geofence untuk cabang <strong>{{ $branch->name }}</strong>.
                    Klik peta atau geser penanda untuk menentukan koordinat.
                </p>
            </div>

            @include('branches.locations._form', [
                'branch' => $branch,
                'locationBuffer' => $locationBuffer,
            ])
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/attendance-location-map.js')
@endpush
