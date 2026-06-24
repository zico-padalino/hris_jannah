@extends('layouts.app')

@section('title', 'Edit Hari Libur')
@section('subtitle', $holiday->name)

@section('content')
    <div class="panel mx-auto max-w-2xl p-6">
        <form method="POST" action="{{ route('holidays.update', $holiday) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('holidays._form')
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('holidays.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
