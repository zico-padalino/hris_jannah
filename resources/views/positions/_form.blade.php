@php($position = $position ?? null)
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium">Kode</label>
        <input name="code" value="{{ old('code', optional($position)->code) }}" required placeholder="Contoh: DR" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Nama Jabatan</label>
        <input name="name" value="{{ old('name', optional($position)->name) }}" required placeholder="Contoh: Dokter" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium">Deskripsi</label>
        <textarea name="description" rows="2" placeholder="Opsional" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('description', optional($position)->description) }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($position)->is_active ?? true)) class="rounded border-slate-300">
            Jabatan aktif
        </label>
    </div>
</div>
