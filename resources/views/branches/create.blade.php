@extends('layouts.app')

@section('title', 'Tambah Cabang')

@section('content')
    <div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('branches.store') }}" class="space-y-4">
            @csrf
            @include('branches._form')
            <div class="flex gap-3 pt-2">
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Simpan</button>
                <a href="{{ route('branches.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
