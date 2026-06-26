@extends('layouts.app')
@section('title', 'Edit Jam Kerja')
@section('subtitle', 'Perbarui jadwal ' . $shift->name)

@section('content')
<div class="shift-page mx-auto max-w-3xl">
    <div class="shift-form-notice panel mb-4 px-4 py-3 text-sm">
        Mengedit jadwal <strong>{{ $shift->code }}</strong> · {{ $shift->employees_count }} pegawai menggunakan jadwal ini
    </div>

    <form method="POST" action="{{ route('shifts.update', $shift) }}" class="panel shift-form-page p-6 lg:p-8">
        @csrf
        @method('PUT')
        @include('shifts._form', ['shift' => $shift])
        <div class="shift-form-page__actions">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="{{ route('shifts.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
