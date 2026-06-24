@extends('layouts.app')
@section('title', 'Edit Jam Kerja')
@section('subtitle', 'Perbarui jadwal ' . $shift->name)

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-4 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900">
        Mengedit jadwal <strong>{{ $shift->code }}</strong> · {{ $shift->employees_count }} pegawai menggunakan jadwal ini
    </div>

    <form method="POST" action="{{ route('shifts.update', $shift) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:p-8">
        @csrf
        @method('PUT')
        @include('shifts._form', ['shift' => $shift])
        <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-6">
            <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">
                Simpan Perubahan
            </button>
            <a href="{{ route('shifts.index') }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
