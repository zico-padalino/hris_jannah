@extends('layouts.app')
@section('title', 'Tambah Jam Kerja')
@section('subtitle', 'Buat jadwal jam masuk, pulang, dan hari kerja baru')

@section('content')
<div class="shift-page mx-auto max-w-3xl">
    <form method="POST" action="{{ route('shifts.store') }}" class="panel shift-form-page p-6 lg:p-8">
        @csrf
        @include('shifts._form')
        <div class="shift-form-page__actions">
            <button type="submit" class="btn-primary">Simpan Jadwal</button>
            <a href="{{ route('shifts.index') }}" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
