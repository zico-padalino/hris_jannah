@extends('layouts.app')
@section('title', 'Jam Kerja Pegawai')
@section('subtitle', 'Atur jadwal jam kerja per pegawai')

@section('content')
<div class="employee-shift-page space-y-4">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="dashboard-stat-card panel dashboard-stat-card--campfire p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">Total Pegawai Aktif</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ $stats['total'] }}</p>
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--emerald p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">Sudah Dijadwalkan</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ $stats['assigned'] }}</p>
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--sky p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">Non Shift</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ $stats['non_shift'] }}</p>
        </div>
        <div class="dashboard-stat-card panel dashboard-stat-card--orange p-4">
            <p class="dashboard-stat-card__label text-sm font-semibold">Belum Dijadwalkan</p>
            <p class="dashboard-stat-card__value mt-1 text-2xl font-extrabold">{{ $stats['unassigned'] }}</p>
        </div>
    </div>

    <div class="filter-bar employee-shift-filter">
        <form method="GET" class="employee-shift-filter__form">
            <label class="employee-shift-filter__field">
                <span class="form-label">Cabang</span>
                <select name="branch_id" class="w-full min-w-[10rem]">
                    <option value="">Semua</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="employee-shift-filter__field">
                <span class="form-label">Departemen</span>
                <select name="department_id" class="w-full min-w-[10rem]">
                    <option value="">Semua</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="employee-shift-filter__field">
                <span class="form-label">Jadwal</span>
                <select name="shift_id" class="w-full min-w-[10rem]">
                    <option value="">Semua jadwal</option>
                    <option value="non_shift" @selected(request('shift_id') === 'non_shift')>Non Shift</option>
                    <option value="unassigned" @selected(request('shift_id') === 'unassigned')>Belum diatur</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" @selected(request('shift_id') == $shift->id)>{{ $shift->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="employee-shift-filter__field employee-shift-filter__field--search">
                <span class="form-label">Cari</span>
                <input name="search" value="{{ request('search') }}" placeholder="Nama / NIK" class="w-full min-w-[10rem]">
            </label>
            <div class="employee-shift-filter__actions">
                <button type="submit" class="btn-primary">Filter</button>
                @if(request()->hasAny(['branch_id', 'department_id', 'shift_id', 'search']))
                    <a href="{{ route('employee-shifts.index') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>
        <div class="filter-bar__actions">
            <a href="{{ route('shifts.index') }}" class="btn-secondary">Kelola Template Jadwal</a>
        </div>
    </div>

    <form id="bulk-shift-form" method="POST" action="{{ route('employee-shifts.bulk') }}" class="employee-shift-bulk hidden">
        @csrf
        <span id="bulk-selected-count" class="employee-shift-bulk__count">0 pegawai dipilih</span>
        <select name="shift_selection" class="employee-shift-bulk__select">
            <option value="non_shift">Non Shift</option>
            <option value="unset">— Hapus jadwal —</option>
            @foreach($shifts as $shift)
                <option value="{{ $shift->id }}">{{ $shift->branch->name ?? 'Global' }} — {{ $shift->name }} ({{ $shift->formattedStartTime() }}–{{ $shift->formattedEndTime() }})</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary employee-shift-bulk__submit">Terapkan ke Terpilih</button>
    </form>

    <div class="panel-table table-mobile-scroll">
        <table class="table-readable min-w-full employee-shift-table">
            <thead>
                <tr>
                    <th class="employee-shift-table__check w-10">
                        <input type="checkbox" id="select-all-employees" class="employee-shift-table__checkbox" aria-label="Pilih semua pegawai">
                    </th>
                    <th>Pegawai</th>
                    <th class="hidden md:table-cell">Cabang</th>
                    <th class="hidden lg:table-cell">Departemen</th>
                    <th>Jadwal Saat Ini</th>
                    <th>Ubah Jadwal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php
                        $applicableShifts = $shifts->filter(
                            fn ($shift) => $shift->branch_id === null || $shift->branch_id === $employee->branch_id
                        );
                    @endphp
                    <tr>
                        <td data-label="">
                            <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" form="bulk-shift-form" class="employee-checkbox employee-shift-table__checkbox" aria-label="Pilih {{ $employee->name }}">
                        </td>
                        <td data-label="Pegawai">
                            <p class="cell-primary">{{ $employee->name }}</p>
                            <p class="cell-secondary">{{ $employee->employee_number }}</p>
                        </td>
                        <td class="hidden md:table-cell" data-label="Cabang">{{ $employee->branch->name ?? '—' }}</td>
                        <td class="hidden lg:table-cell" data-label="Departemen">{{ $employee->department->name ?? '—' }}</td>
                        <td data-label="Jadwal Saat Ini">
                            @if($employee->is_non_shift)
                                <span class="employee-shift-badge employee-shift-badge--neutral">Non Shift</span>
                            @elseif($employee->shift)
                                <div class="employee-shift-current">
                                    <span class="employee-shift-badge employee-shift-badge--active">{{ $employee->shift->name }}</span>
                                    <span class="employee-shift-current__time">{{ $employee->shift->formattedStartTime() }}–{{ $employee->shift->formattedEndTime() }}</span>
                                    <span class="employee-shift-current__days">{{ $employee->shift->workDaysLabel(true) }}</span>
                                </div>
                            @else
                                <span class="employee-shift-badge employee-shift-badge--pending">Belum diatur</span>
                            @endif
                        </td>
                        <td data-label="Ubah Jadwal">
                            <form method="POST" action="{{ route('employee-shifts.update', $employee) }}" class="employee-shift-row-form">
                                @csrf
                                @method('PUT')
                                <select name="shift_selection" class="employee-shift-row-form__select">
                                    <option value="non_shift" @selected($employee->is_non_shift)>Non Shift</option>
                                    <option value="unset" @selected(!$employee->is_non_shift && !$employee->shift_id)>— Kosongkan —</option>
                                    @foreach($applicableShifts as $shift)
                                        <option value="{{ $shift->id }}" @selected(!$employee->is_non_shift && $employee->shift_id == $shift->id)>
                                            {{ $shift->name }} ({{ $shift->formattedStartTime() }}–{{ $shift->formattedEndTime() }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-primary employee-shift-row-form__btn">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="employee-shift-table__empty">Tidak ada pegawai ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($employees->hasPages())
        <div>{{ $employees->links() }}</div>
    @endif
</div>
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
        bulkForm.classList.toggle('employee-shift-bulk--visible', selected > 0);
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
