<form method="POST" action="{{ route('branch-locations.store', $branch) }}" class="space-y-3">
    @csrf
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Nama lokasi</label>
        <input name="name" value="{{ old('name') }}" placeholder="Contoh: Lobby IGD" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Latitude</label>
            <input name="latitude" id="loc-latitude" value="{{ old('latitude') }}" step="any" placeholder="-6.118837" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Longitude</label>
            <input name="longitude" id="loc-longitude" value="{{ old('longitude') }}" step="any" placeholder="106.153679" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
    </div>
    <button type="button" id="btn-current-location" class="w-full rounded-lg border border-teal-300 bg-teal-50 px-3 py-2 text-sm text-teal-800 hover:bg-teal-100">
        Ambil Lokasi Saat Ini
    </button>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Radius (meter)</label>
        <input name="radius_meters" type="number" min="10" max="5000" value="{{ old('radius_meters', 100) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-slate-300">
        Aktif
    </label>
    <div class="flex flex-wrap gap-3 pt-2">
        <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Simpan Lokasi</button>
        <a href="{{ route('branches.show', $branch) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</a>
    </div>
</form>
