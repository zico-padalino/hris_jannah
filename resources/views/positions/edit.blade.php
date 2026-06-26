@extends('layouts.app')

@section('title', 'Edit Jabatan')
@section('subtitle', $position->name)

@section('content')
    <div class="panel mx-auto max-w-2xl p-6">
        @if($position->employees_count > 0)
            <p class="mb-4 app-notice">
                Jabatan ini digunakan oleh {{ $position->employees_count }} pegawai.
            </p>
        @endif
        <form method="POST" action="{{ route('positions.update', $position) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('positions._form')
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('positions.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
