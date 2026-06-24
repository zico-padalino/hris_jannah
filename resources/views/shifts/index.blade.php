@extends('layouts.app')
@section('title', 'Template Jam Kerja')
@section('subtitle', 'Kelola jam masuk, jam pulang, hari kerja, dan toleransi keterlambatan')

@section('content')
<div class="mb-4 grid gap-2 sm:grid-cols-3">
    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm">
        <p class="text-xs text-slate-500">Total Jadwal</p>
        <p class="text-lg font-bold text-slate-900">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 shadow-sm">
        <p class="text-xs text-emerald-700">Jadwal Aktif</p>
        <p class="text-lg font-bold text-emerald-700">{{ $stats['active'] }}</p>
    </div>
    <div class="rounded-lg border border-teal-200 bg-teal-50 px-3 py-2 shadow-sm">
        <p class="text-xs text-teal-700">Pegawai Terjadwal</p>
        <p class="text-lg font-bold text-teal-700">{{ $stats['employees'] }}</p>
    </div>
</div>

<div class="mb-4 flex flex-col gap-2 rounded-lg border border-slate-200 bg-white p-3 shadow-sm sm:flex-row sm:items-center sm:justify-between">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <label class="text-sm font-medium text-slate-600">Cabang</label>
        <select name="branch_id" class="min-w-[140px] rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:border-teal-500 focus:outline-none">
            <option value="">Semua cabang</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-xs font-medium text-white">Filter</button>
        @if(request()->filled('branch_id'))
            <a href="{{ route('shifts.index') }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50">Reset</a>
        @endif
    </form>
    <div class="flex flex-wrap gap-2">
        @moduleAction('shift_templates', 'create')
            <a href="{{ route('shifts.create') }}" class="inline-flex items-center justify-center rounded-md bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-800">
                + Tambah
            </a>
        @endmoduleAction
        <a href="{{ route('employee-shifts.index') }}" class="inline-flex items-center justify-center rounded-md border border-teal-300 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-800 hover:bg-teal-100">
            Atur Pegawai
        </a>
    </div>
</div>

@if($shifts->isEmpty())
    <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white px-6 py-16 text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-teal-50 text-3xl">🕐</div>
        <h3 class="text-lg font-semibold text-slate-900">Belum ada jadwal jam kerja</h3>
        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
            Buat jadwal pertama untuk menentukan jam masuk, jam pulang, dan hari kerja pegawai.
        </p>
        @moduleAction('shift_templates', 'create')
            <a href="{{ route('shifts.create') }}" class="mt-6 inline-flex rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-teal-800">
                Buat Jadwal Sekarang
            </a>
        @endmoduleAction
    </div>
@else
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($shifts as $shift)
            @include('shifts._card', ['shift' => $shift])
        @endforeach
    </div>
    <div class="mt-6">{{ $shifts->links() }}</div>
@endif
@endsection
