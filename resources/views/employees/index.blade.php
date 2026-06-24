@extends('layouts.app')

@section('title', 'Pegawai')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
        <form method="GET" class="filter-bar flex w-full flex-col gap-3 !mb-0 sm:flex-row sm:flex-wrap sm:items-end">
            <label class="min-w-0 flex-1 sm:min-w-[12rem]">
                <span class="form-label">Cari Pegawai</span>
                <input name="search" value="{{ request('search') }}" placeholder="Nama atau NIK..." class="w-full">
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
        @moduleAction('employees', 'create')
            <a href="{{ route('employees.create') }}" class="btn-primary w-full shrink-0 sm:w-auto">+ Tambah Pegawai</a>
        @endmoduleAction
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Cabang</th>
                    <th>Departemen</th>
                    <th>Jabatan</th>
                    <th>Wajah</th>
                    <th>Gaji Pokok</th>
                    <th class="cell-actions-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td class="font-bold">{{ $employee->employee_number }}</td>
                        <td class="font-bold">{{ $employee->name }}</td>
                        <td>{{ $employee->branch->name }}</td>
                        <td>{{ $employee->department->name ?? '—' }}</td>
                        <td>{{ $employee->position->name ?? '—' }}</td>
                        <td>{{ $employee->faces->count() }} terdaftar</td>
                        <td>Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'employees',
                                'show' => route('employees.show', $employee),
                                'edit' => route('employees.edit', $employee),
                                'delete' => route('employees.destroy', $employee),
                                'deleteConfirm' => 'Hapus pegawai '.$employee->name.'?',
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="py-10 text-center font-semibold text-slate-600">Belum ada pegawai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $employees->links() }}</div>
@endsection
