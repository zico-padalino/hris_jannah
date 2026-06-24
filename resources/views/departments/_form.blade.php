@php($department = $department ?? null)
<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium">Cabang</label>
        <select name="branch_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
            @foreach($branches as $branch)
                <option
                    value="{{ $branch->id }}"
                    @selected(old('branch_id', optional($department)->branch_id ?? ($selectedBranchId ?? null)) == $branch->id)
                >
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Kode</label>
        <input name="code" value="{{ old('code', optional($department)->code) }}" required placeholder="Contoh: IGD" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Nama Departemen</label>
        <input name="name" value="{{ old('name', optional($department)->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($department)->is_active ?? true)) class="rounded border-slate-300">
            Departemen aktif
        </label>
    </div>
</div>
