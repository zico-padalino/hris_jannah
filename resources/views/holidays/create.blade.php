@extends('layouts.app')

@section('title', 'Tambah Hari Libur')

@section('content')
    <div class="panel mx-auto max-w-2xl p-6">
        <form method="POST" action="{{ route('holidays.store') }}" class="space-y-4">
            @csrf
            @include('holidays._form')
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan</button>
                <a href="{{ route('holidays.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
