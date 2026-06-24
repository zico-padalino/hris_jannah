@extends('layouts.app')

@section('title', 'Cabang RS')
@section('subtitle', 'Kelola cabang rumah sakit multi-lokasi')

@section('content')
    <div class="mb-6 flex justify-stretch sm:justify-end">
        @moduleAction('branches', 'create')
            <a href="{{ route('branches.create') }}" class="btn-primary w-full sm:w-auto">+ Tambah Cabang</a>
        @endmoduleAction
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Kota</th>
                    <th>Pegawai</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th class="cell-actions-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                    <tr>
                        <td class="font-bold">{{ $branch->code }}</td>
                        <td class="font-bold">{{ $branch->name }}</td>
                        <td>{{ $branch->city ?? '—' }}</td>
                        <td>{{ $branch->employees_count }}</td>
                        <td>{{ $branch->locations_count }}</td>
                        <td>
                            <span class="badge-readable {{ $branch->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-200 text-slate-800' }}">
                                {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'branches',
                                'show' => route('branches.show', $branch),
                                'edit' => route('branches.edit', $branch),
                                'location' => route('branch-locations.create', $branch),
                                'locationLabel' => 'Tambah Lokasi',
                                'delete' => route('branches.destroy', $branch),
                                'deleteConfirm' => 'Hapus cabang '.$branch->name.'?',
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-10 text-center font-semibold text-slate-600">Belum ada cabang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $branches->links() }}</div>
@endsection
