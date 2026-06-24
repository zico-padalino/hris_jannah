@extends('layouts.app')
@section('title', 'Tambah Jam Kerja')
@section('subtitle', 'Buat jadwal jam masuk, pulang, dan hari kerja baru')

@section('content')
<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ route('shifts.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
        @csrf
        @include('shifts._form')
        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6">
            <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">
                Simpan Jadwal
            </button>
            <a href="{{ route('shifts.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
