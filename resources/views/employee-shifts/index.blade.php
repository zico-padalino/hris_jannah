@extends('layouts.app')
@section('title', 'Jam Kerja Pegawai')
@section('subtitle', 'Atur jadwal jam kerja per pegawai')

@section('content')
<div class="mb-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-sm">
        <p class="text-xs text-slate-500">Total Pegawai Aktif</p>
        <p class="text-lg font-bold text-slate-900">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-lg border border-teal-200 bg-teal-50 px-3 py-2 shadow-sm">
        <p class="text-xs text-teal-700">Sudah Dijadwalkan</p>
        <p class="text-lg font-bold text-teal-700">{{ $stats['assigned'] }}</p>
    </div>
    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 shadow-sm">
        <p class="text-xs text-slate-600">Non Shift</p>
        <p class="text-lg font-bold text-slate-800">{{ $stats['non_shift'] }}</p>
    </div>
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 shadow-sm">
        <p class="text-xs text-amber-800">Belum Dijadwalkan</p>
        <p class="text-lg font-bold text-amber-800">{{ $stats['unassigned'] }}</p>
    </div>
</div>

<div class="mb-4 rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
    <form method="GET" class="flex flex-wrap items-end gap-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Cabang</label>
            <select name="branch_id" class="min-w-[140px] rounded-md border border-slate-300 px-2 py-1.5 text-xs">
                <option value="">Semua</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Departemen</label>
            <select name="department_id" class="min-w-[140px] rounded-md border border-slate-300 px-2 py-1.5 text-xs">
                <option value="">Semua</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Jadwal</label>
            <select name="shift_id" class="min-w-[140px] rounded-md border border-slate-300 px-2 py-1.5 text-xs">
                <option value="">Semua jadwal</option>
                <option value="non_shift" @selected(request('shift_id') === 'non_shift')>Non Shift</option>
                <option value="unassigned" @selected(request('shift_id') === 'unassigned')>Belum diatur</option>
                @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}" @selected(request('shift_id') == $shift->id)>{{ $shift->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Cari</label>
            <input name="search" value="{{ request('search') }}" placeholder="Nama / NIK" class="min-w-[140px] rounded-md border border-slate-300 px-2 py-1.5 text-xs">
        </div>
        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-xs font-medium text-white">Filter</button>
        @if(request()->hasAny(['branch_id', 'department_id', 'shift_id', 'search']))
            <a href="{{ route('employee-shifts.index') }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50">Reset</a>
        @endif
        <a href="{{ route('shifts.index') }}" class="ml-auto rounded-md border border-teal-300 px-3 py-1.5 text-xs font-medium text-teal-800 hover:bg-teal-50">Kelola Template Jadwal</a>
    </form>
</div>

<form id="bulk-shift-form" method="POST" action="{{ route('employee-shifts.bulk') }}" class="mb-3 hidden items-center gap-2 rounded-lg border border-teal-200 bg-teal-50 p-3">
    @csrf
    <span id="bulk-selected-count" class="text-xs font-medium text-teal-900">0 pegawai dipilih</span>
    <select name="shift_selection" class="rounded-md border border-slate-300 px-2 py-1.5 text-xs">
        <option value="non_shift">Non Shift</option>
        <option value="unset">— Hapus jadwal —</option>
        @foreach($shifts as $shift)
            <option value="{{ $shift->id }}">{{ $shift->branch->name ?? 'Global' }} — {{ $shift->name }} ({{ $shift->formattedStartTime() }}–{{ $shift->formattedEndTime() }})</option>
        @endforeach
    </select>
    <button type="submit" class="rounded-md bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-800">Terapkan ke Terpilih</button>
</form>

<div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
    <table class="table-readable min-w-full">
        <thead class="bg-slate-50 text-left text-xs text-slate-500">
            <tr>
                <th class="px-4 py-3 w-10">
                    <input type="checkbox" id="select-all-employees" class="rounded border-slate-300">
                </th>
                <th class="px-4 py-3">Pegawai</th>
                <th class="px-4 py-3 hidden md:table-cell">Cabang</th>
                <th class="px-4 py-3 hidden lg:table-cell">Departemen</th>
                <th class="px-4 py-3">Jadwal Saat Ini</th>
                <th class="px-4 py-3">Ubah Jadwal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                @php
                    $applicableShifts = $shifts->filter(
                        fn ($shift) => $shift->branch_id === null || $shift->branch_id === $employee->branch_id
                    );
                @endphp
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-3">
                        <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" form="bulk-shift-form" class="employee-checkbox rounded border-slate-300">
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-900">{{ $employee->name }}</p>
                        <p class="text-xs text-slate-500">{{ $employee->employee_number }}</p>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-600">{{ $employee->branch->name ?? '-' }}</td>
                    <td class="px-4 py-3 hidden lg:table-cell text-slate-600">{{ $employee->department->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($employee->is_non_shift)
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">Non Shift</span>
                        @elseif($employee->shift)
                            <div class="text-xs">
                                <span class="font-semibold text-teal-800">{{ $employee->shift->name }}</span>
                                <span class="text-slate-500">{{ $employee->shift->formattedStartTime() }}–{{ $employee->shift->formattedEndTime() }}</span>
                                <span class="block text-slate-400">{{ $employee->shift->workDaysLabel(true) }}</span>
                            </div>
                        @else
                            <span class="rounded bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800">Belum diatur</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('employee-shifts.update', $employee) }}" class="flex flex-wrap items-center gap-1.5">
                            @csrf
                            @method('PUT')
                            <select name="shift_selection" class="max-w-[180px] rounded-md border border-slate-300 px-2 py-1 text-xs">
                                <option value="non_shift" @selected($employee->is_non_shift)>Non Shift</option>
                                <option value="unset" @selected(!$employee->is_non_shift && !$employee->shift_id)>— Kosongkan —</option>
                                @foreach($applicableShifts as $shift)
                                    <option value="{{ $shift->id }}" @selected(!$employee->is_non_shift && $employee->shift_id == $shift->id)>
                                        {{ $shift->name }} ({{ $shift->formattedStartTime() }}–{{ $shift->formattedEndTime() }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="rounded-md bg-slate-800 px-2 py-1 text-xs font-medium text-white hover:bg-slate-900">Simpan</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-slate-500">Tidak ada pegawai ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $employees->links() }}</div>
@endsection

@push('scripts')
<script>
    const selectAll = document.getElementById('select-all-employees');
    const checkboxes = () => Array.from(document.querySelectorAll('.employee-checkbox'));
    const bulkForm = document.getElementById('bulk-shift-form');
    const bulkCount = document.getElementById('bulk-selected-count');

    function syncBulkBar() {
        const selected = checkboxes().filter((input) => input.checked).length;
        bulkForm.classList.toggle('hidden', selected === 0);
        bulkForm.classList.toggle('flex', selected > 0);
        bulkCount.textContent = `${selected} pegawai dipilih`;
    }

    selectAll?.addEventListener('change', () => {
        checkboxes().forEach((input) => {
            input.checked = selectAll.checked;
        });
        syncBulkBar();
    });

    checkboxes().forEach((input) => input.addEventListener('change', syncBulkBar));
</script>
@endpush
