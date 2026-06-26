@php
    $locationBuffer = $locationBuffer ?? 0;
    $initialLat = old('latitude');
    $initialLng = old('longitude');
    $initialRadius = old('radius_meters', 100);
@endphp

<form method="POST" action="{{ route('branch-locations.store', $branch) }}" class="attendance-location-form">
    @csrf
    <div class="attendance-location-form__grid">
        <div class="attendance-location-form__fields">
            <fieldset class="attendance-location-form__group">
                <legend class="attendance-location-form__legend">Informasi lokasi</legend>
                <label class="attendance-location-form__label">
                    Nama lokasi
                    <input
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Contoh: Lobby IGD"
                        required
                        class="w-full"
                    >
                </label>
            </fieldset>

            <fieldset class="attendance-location-form__group">
                <legend class="attendance-location-form__legend">Koordinat GPS</legend>
                <div class="attendance-location-form__coords">
                    <label class="attendance-location-form__label">
                        Latitude
                        <input
                            name="latitude"
                            id="loc-latitude"
                            value="{{ $initialLat }}"
                            step="any"
                            placeholder="-6.118837"
                            required
                            class="w-full font-mono text-sm"
                        >
                    </label>
                    <label class="attendance-location-form__label">
                        Longitude
                        <input
                            name="longitude"
                            id="loc-longitude"
                            value="{{ $initialLng }}"
                            step="any"
                            placeholder="106.153679"
                            required
                            class="w-full font-mono text-sm"
                        >
                    </label>
                </div>
            </fieldset>

            <fieldset class="attendance-location-form__group">
                <legend class="attendance-location-form__legend">Area geofence</legend>
                <label class="attendance-location-form__label">
                    Radius (meter)
                    <input
                        name="radius_meters"
                        id="loc-radius"
                        type="number"
                        min="10"
                        max="5000"
                        value="{{ $initialRadius }}"
                        required
                        class="w-full"
                    >
                </label>
                <p class="attendance-location-form__field-hint">Minimal 10 m. Pegawai hanya bisa absen di dalam lingkaran radius ini.</p>

                <label class="attendance-location-form__toggle">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                    <span class="attendance-location-form__toggle-box" aria-hidden="true"></span>
                    <span>
                        <span class="attendance-location-form__toggle-title">Lokasi aktif</span>
                        <span class="attendance-location-form__toggle-desc">Nonaktifkan jika lokasi sementara tidak dipakai.</span>
                    </span>
                </label>
            </fieldset>

            <div class="attendance-location-form__actions">
                <button type="submit" class="btn-primary">Simpan Lokasi</button>
                <a href="{{ route('branches.show', $branch) }}" class="btn-secondary">Batal</a>
            </div>
        </div>

        <div class="attendance-location-form__map">
            @include('partials.attendance-location-map', [
                'mapId' => 'branch-location-map',
                'latInputId' => 'loc-latitude',
                'lngInputId' => 'loc-longitude',
                'radiusInputId' => 'loc-radius',
                'initialLat' => $initialLat,
                'initialLng' => $initialLng,
                'initialRadius' => $initialRadius,
                'existingLocations' => $branch->locations ?? [],
                'bufferMeters' => $locationBuffer,
            ])
        </div>
    </div>
</form>
