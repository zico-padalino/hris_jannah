@extends('layouts.app')

@section('title', 'Hari Libur')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
        <form method="GET" class="filter-bar flex w-full flex-col gap-3 !mb-0 sm:flex-row sm:flex-wrap sm:items-end">
            <label class="min-w-0 sm:min-w-[10rem]">
                <span class="form-label">Cabang</span>
                <select name="branch_id" class="w-full">
                    <option value="">Semua cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="btn-primary w-full sm:w-auto">Filter</button>
        </form>
        @moduleAction('holidays', 'create')
            <a href="{{ route('holidays.create') }}" class="btn-primary w-full shrink-0 sm:w-auto">+ Tambah Hari Libur</a>
        @endmoduleAction
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama</th>
                    <th>Cabang</th>
                    <th>Status</th>
                    <th class="cell-actions-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $holiday)
                    <tr>
                        <td class="font-bold whitespace-nowrap">{{ $holiday->date->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $holiday->name }}</td>
                        <td>{{ $holiday->branch->name ?? 'Semua Cabang' }}</td>
                        <td>
                            <span class="badge-readable {{ $holiday->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-200 text-slate-800' }}">
                                {{ $holiday->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'holidays',
                                'edit' => route('holidays.edit', $holiday),
                                'delete' => route('holidays.destroy', $holiday),
                                'deleteConfirm' => 'Hapus hari libur '.$holiday->name.'?',
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center font-semibold text-slate-600">Belum ada hari libur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $holidays->links() }}</div>
@endsection
