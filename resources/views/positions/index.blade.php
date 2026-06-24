@extends('layouts.app')

@section('title', 'Jabatan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
        <form method="GET" class="filter-bar flex w-full flex-col gap-3 !mb-0 sm:flex-row sm:flex-wrap sm:items-end">
            <label class="min-w-0 flex-1 sm:min-w-[12rem]">
                <span class="form-label">Cari</span>
                <input name="search" value="{{ request('search') }}" placeholder="Nama atau kode..." class="w-full">
            </label>
            <button type="submit" class="btn-primary w-full sm:w-auto">Cari</button>
        </form>
        @moduleAction('positions', 'create')
            <a href="{{ route('positions.create') }}" class="btn-primary w-full shrink-0 sm:w-auto">+ Tambah Jabatan</a>
        @endmoduleAction
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Jabatan</th>
                    <th>Deskripsi</th>
                    <th>Pegawai</th>
                    <th>Status</th>
                    <th class="cell-actions-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($positions as $position)
                    <tr>
                        <td class="font-bold">{{ $position->code }}</td>
                        <td class="font-bold">{{ $position->name }}</td>
                        <td>{{ $position->description ?: '—' }}</td>
                        <td>{{ $position->employees_count }}</td>
                        <td>
                            <span class="badge-readable {{ $position->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-200 text-slate-800' }}">
                                {{ $position->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'positions',
                                'edit' => route('positions.edit', $position),
                                'delete' => route('positions.destroy', $position),
                                'deleteConfirm' => 'Hapus jabatan '.$position->name.'?',
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center font-semibold text-slate-600">Belum ada jabatan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $positions->links() }}</div>
@endsection
