@extends('layouts.app')

@section('title', $branch->name)
@section('subtitle', $branch->code)

@section('content')
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('branches.edit', $branch) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Edit Cabang</a>
        <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Hapus cabang ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm text-red-700 hover:bg-red-50">Hapus</button>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold">Informasi Cabang</h2>
        <dl class="grid gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">Alamat</dt><dd>{{ $branch->address ?? '-' }}</dd></div>
            <div><dt class="text-slate-500">Telepon</dt><dd>{{ $branch->phone ?? '-' }}</dd></div>
            <div><dt class="text-slate-500">Kota</dt><dd>{{ $branch->city ?? '-' }}</dd></div>
            <div><dt class="text-slate-500">Status</dt><dd>{{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
        </dl>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-semibold">Lokasi Absensi ({{ $branch->locations->count() }})</h2>
        <div class="space-y-4">
            @forelse($branch->locations as $location)
                <form method="POST" action="{{ route('branch-locations.update', $location) }}" class="rounded-lg border border-slate-200 p-4">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-3 md:grid-cols-5">
                        <input name="name" value="{{ $location->name }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <input name="latitude" value="{{ $location->latitude }}" step="any" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <input name="longitude" value="{{ $location->longitude }}" step="any" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <input name="radius_meters" value="{{ $location->radius_meters }}" type="number" min="10" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-1 text-sm">
                                <input type="checkbox" name="is_active" value="1" @checked($location->is_active) class="rounded border-slate-300">
                                Aktif
                            </label>
                            <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs text-white">Update</button>
                        </div>
                    </div>
                </form>
                <form method="POST" action="{{ route('branch-locations.destroy', $location) }}" class="-mt-2 text-right">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs text-red-600 hover:underline" onclick="return confirm('Hapus lokasi ini?')">Hapus lokasi</button>
                </form>
            @empty
                <p class="text-sm text-slate-500">Belum ada lokasi absensi untuk cabang ini.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold">Departemen</h2>
            <a href="{{ route('departments.create', ['branch_id' => $branch->id]) }}" class="text-sm font-semibold text-teal-700 hover:underline">+ Tambah di halaman Departemen</a>
        </div>
        <form method="POST" action="{{ route('departments.store') }}" class="mb-4 grid gap-3 md:grid-cols-4">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <input name="code" placeholder="Kode (IGD)" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <input name="name" placeholder="Nama departemen" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm md:col-span-2">
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white hover:bg-teal-800">Tambah</button>
        </form>
        <div class="space-y-3">
            @forelse($branch->departments as $department)
                <div class="rounded-lg border border-slate-200 p-4">
                    <form method="POST" action="{{ route('departments.update', $department) }}" class="grid gap-3 md:grid-cols-5">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                        <input name="code" value="{{ $department->code }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <input name="name" value="{{ $department->name }}" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" @checked($department->is_active) class="rounded border-slate-300">
                            Aktif
                        </label>
                        <button type="submit" class="rounded-lg bg-slate-800 px-3 py-2 text-xs text-white">Update</button>
                    </form>
                    <div class="mt-2 flex items-center justify-between gap-3">
                        <a href="{{ route('departments.edit', $department) }}" class="text-xs font-semibold text-teal-700 hover:underline">Edit lengkap</a>
                        <form method="POST" action="{{ route('departments.destroy', $department) }}" onsubmit="return confirm('Hapus departemen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada departemen.</p>
            @endforelse
        </div>
    </div>
@endsection
