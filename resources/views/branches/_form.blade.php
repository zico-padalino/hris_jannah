@php($branch = $branch ?? null)
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="mb-1 block text-sm font-medium">Kode</label>
        <input name="code" value="{{ old('code', optional($branch)->code) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Nama Cabang</label>
        <input name="name" value="{{ old('name', optional($branch)->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div class="sm:col-span-2">
        <label class="mb-1 block text-sm font-medium">Alamat</label>
        <textarea name="address" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2">{{ old('address', optional($branch)->address) }}</textarea>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Telepon</label>
        <input name="phone" value="{{ old('phone', optional($branch)->phone) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">Kota</label>
        <input name="city" value="{{ old('city', optional($branch)->city) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2">
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', optional($branch)->is_active ?? true)) class="rounded border-slate-300">
            Cabang aktif
        </label>
    </div>
</div>
