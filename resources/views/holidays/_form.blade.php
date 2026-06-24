@php($holiday = $holiday ?? null)
<div class="grid gap-4">
    <div>
        <label class="mb-1 block text-sm font-medium">Cabang</label>
        <select name="branch_id" class="w-full rounded-lg border border-slate-300 px-3 py-2">
            <option value="">Semua Cabang</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected(old('branch_id', optional($holiday)->branch_id) == $branch->id)>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500">Kosongkan untuk hari libur nasional / seluruh cabang.</p>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Nama Hari Libur</label>
        <input name="name" value="{{ old('name', optional($holiday)->name) }}" required placeholder="Contoh: Idul Fitri" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Tanggal</label>
        <input name="date" type="date" value="{{ old('date', optional($holiday)->date?->format('Y-m-d')) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($holiday)->is_active ?? true)) class="rounded border-slate-300">
            Hari libur aktif
        </label>
    </div>
</div>
