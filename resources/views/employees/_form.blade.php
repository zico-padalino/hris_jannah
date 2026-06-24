@php($employee = $employee ?? null)
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium">Cabang</label>
        <select name="branch_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', optional($employee)->branch_id) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Departemen</label>
        <select name="department_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="">- Pilih -</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', optional($employee)->department_id) == $department->id)>{{ $department->branch->name }} — {{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Shift Kerja</label>
        @php($shiftSelection = old('shift_selection', optional($employee)->shiftSelection() ?? 'unset'))
        <select name="shift_selection" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="non_shift" @selected($shiftSelection === 'non_shift')>Non Shift</option>
            <option value="unset" @selected($shiftSelection === 'unset')>Belum diatur</option>
            @foreach(($shifts ?? []) as $shift)
                <option value="{{ $shift->id }}" @selected($shiftSelection === (string) $shift->id)>{{ $shift->branch->name ?? 'Global' }} — {{ $shift->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Pilih <strong>Non Shift</strong> untuk pegawai yang tidak mengikuti jadwal shift tetap.</p>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">NIK / No. Pegawai</label>
        <input name="employee_number" value="{{ old('employee_number', optional($employee)->employee_number) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">PIN Mesin Fingerprint</label>
        <input name="fingerprint_pin" value="{{ old('fingerprint_pin', optional($employee)->fingerprint_pin) }}" placeholder="Contoh: 1, 2, 1001" class="w-full rounded-lg border border-slate-300 px-3 py-2">
        <p class="mt-1 text-xs text-slate-500">Harus sama dengan User ID / PIN di mesin ZKTeco X100-C.</p>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Nama Lengkap</label>
        <input name="name" value="{{ old('name', optional($employee)->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Email</label>
        <input name="email" type="email" value="{{ old('email', optional($employee)->email) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Telepon</label>
        <input name="phone" value="{{ old('phone', optional($employee)->phone) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium">Alamat</label>
        <textarea name="address" rows="3" placeholder="Jl. ..., RT/RW, Kelurahan, Kota" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('address', optional($employee)->address) }}</textarea>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Jabatan</label>
        <select name="position_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="">- Pilih Jabatan -</option>
            @foreach(($positions ?? []) as $position)
                <option value="{{ $position->id }}" @selected(old('position_id', optional($employee)->position_id) == $position->id)>
                    {{ $position->code }} — {{ $position->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Status Kepegawaian</label>
        <select name="employment_status" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @foreach(['permanent' => 'Tetap', 'contract' => 'Kontrak', 'honorary' => 'Honorer'] as $value => $label)
                <option value="{{ $value }}" @selected(old('employment_status', optional($employee)->employment_status ?? 'permanent') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Gaji Pokok</label>
        <input name="base_salary" type="number" min="0" value="{{ old('base_salary', optional($employee)->base_salary ?? 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Tanggal Bergabung</label>
        <input name="join_date" type="date" value="{{ old('join_date', optional($employee)->join_date?->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Mulai Kontrak</label>
        <input name="contract_start_date" type="date" value="{{ old('contract_start_date', optional($employee)->contract_start_date?->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
        <p class="mt-1 text-xs text-slate-500">Diisi untuk pegawai status Kontrak.</p>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Akhir Kontrak</label>
        <input name="contract_end_date" type="date" value="{{ old('contract_end_date', optional($employee)->contract_end_date?->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($employee)->is_active ?? true)) class="rounded border-slate-300">
            Pegawai aktif
        </label>
        <p class="mt-2 text-xs text-slate-500">{{ __('pages.users.employee_sync_hint') }}</p>
    </div>
</div>
