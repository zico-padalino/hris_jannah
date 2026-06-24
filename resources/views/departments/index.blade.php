@extends('layouts.app')

@section('title', 'Departemen')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
        <form method="GET" class="filter-bar flex w-full flex-col gap-3 !mb-0 sm:flex-row sm:flex-wrap sm:items-end">
            <label class="min-w-0 flex-1 sm:min-w-[12rem]">
                <span class="form-label">Cari</span>
                <input name="search" value="{{ request('search') }}" placeholder="Nama atau kode..." class="w-full">
            </label>
            <label class="min-w-0 sm:min-w-[10rem]">
                <span class="form-label">Cabang</span>
                <select name="branch_id" class="w-full">
                    <option value="">Semua cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn-primary w-full sm:w-auto">Cari</button>
        </form>
        @moduleAction('departments', 'create')
            <a href="{{ route('departments.create') }}" class="btn-primary w-full shrink-0 sm:w-auto">+ Tambah Departemen</a>
        @endmoduleAction
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Cabang</th>
                    <th>Pegawai</th>
                    <th>Status</th>
                    <th class="cell-actions-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $department)
                    <tr>
                        <td class="font-bold">{{ $department->code }}</td>
                        <td class="font-bold">{{ $department->name }}</td>
                        <td>{{ $department->branch->name }}</td>
                        <td>{{ $department->employees_count }}</td>
                        <td>
                            <span class="badge-readable {{ $department->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-200 text-slate-800' }}">
                                {{ $department->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'departments',
                                'edit' => route('departments.edit', $department),
                                'delete' => route('departments.destroy', $department),
                                'deleteConfirm' => 'Hapus departemen '.$department->name.'?',
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center font-semibold text-slate-600">Belum ada departemen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $departments->links() }}</div>
@endsection
