@extends('layouts.app')
@section('title', 'Template Jam Kerja')
@section('subtitle', 'Kelola jam masuk, jam pulang, hari kerja, dan toleransi keterlambatan')

@section('content')
<div class="shift-page space-y-4">
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="dashboard-stat-card panel dashboard-stat-card--campfire p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">Total Jadwal</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ $stats['total'] }}</p>
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--emerald p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">Jadwal Aktif</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ $stats['active'] }}</p>
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--orange p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">Pegawai Terjadwal</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ $stats['employees'] }}</p>
        </div>
    </div>

    <div class="filter-bar flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <form method="GET" class="flex flex-wrap items-end gap-2">
            <label class="min-w-[10rem]">
                <span class="form-label">Cabang</span>
                <select name="branch_id" class="w-full min-w-[10rem]">
                    <option value="">Semua cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn-primary">Filter</button>
            @if(request()->filled('branch_id'))
                <a href="{{ route('shifts.index') }}" class="btn-secondary">Reset</a>
            @endif
        </form>
        <div class="filter-bar__actions flex flex-wrap gap-2">
            @moduleAction('shift_templates', 'create')
                <a href="{{ route('shifts.create') }}" class="btn-primary">+ Tambah</a>
            @endmoduleAction
            <a href="{{ route('employee-shifts.index') }}" class="btn-secondary">Atur Pegawai</a>
        </div>
    </div>

    @if($shifts->isEmpty())
        <div class="shift-empty panel">
            <div class="shift-empty__icon" aria-hidden="true">🕐</div>
            <h3 class="shift-empty__title">Belum ada jadwal jam kerja</h3>
            <p class="shift-empty__desc">
                Buat jadwal pertama untuk menentukan jam masuk, jam pulang, dan hari kerja pegawai.
            </p>
            @moduleAction('shift_templates', 'create')
                <a href="{{ route('shifts.create') }}" class="btn-primary mt-6">Buat Jadwal Sekarang</a>
            @endmoduleAction
        </div>
    @else
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($shifts as $shift)
                @include('shifts._card', ['shift' => $shift])
            @endforeach
        </div>
        @if($shifts->hasPages())
            <div class="mt-4">{{ $shifts->links() }}</div>
        @endif
    @endif
</div>
@endsection
