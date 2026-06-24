@extends('layouts.app')

@section('title', 'Tambah Lokasi Absensi')
@section('subtitle', $branch->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('branches.show', $branch) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">← Kembali ke Detail Cabang</a>
    </div>

    <div class="mx-auto max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-1 text-lg font-semibold">Tambah Lokasi Absensi</h2>
        <p class="mb-4 text-sm text-slate-500">Atur titik GPS dan radius geofence tempat pegawai boleh absen di cabang <strong>{{ $branch->name }}</strong>.</p>

        @include('branches.locations._form', ['branch' => $branch])
    </div>
@endsection

@push('scripts')
    @include('branches.locations._gps-script')
@endpush
