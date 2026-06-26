@props([
    'mapId' => 'attendance-location-map',
    'latInputId' => 'loc-latitude',
    'lngInputId' => 'loc-longitude',
    'radiusInputId' => 'loc-radius',
    'bufferInputSelector' => null,
    'bufferMeters' => null,
    'initialLat' => null,
    'initialLng' => null,
    'initialRadius' => 100,
    'existingLocations' => [],
    'showSearch' => true,
    'showGpsButton' => true,
])

@php
    $existingJson = collect($existingLocations)->map(function ($location) {
        if (is_object($location)) {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'radius_meters' => (int) $location->radius_meters,
                'is_active' => (bool) $location->is_active,
            ];
        }

        return $location;
    })->values()->all();

    $hasExisting = count($existingJson) > 0;
    $hasBuffer = $bufferMeters !== null && (int) $bufferMeters > 0;
@endphp

<div class="attendance-location-map-panel">
    <div class="attendance-location-map-panel__header">
        <h3 class="attendance-location-map-panel__title">{{ __('pages.settings.attendance_map_panel_title') }}</h3>
        <p class="attendance-location-map-panel__desc">{{ __('pages.settings.attendance_map_panel_desc') }}</p>
    </div>

    <div
        class="attendance-location-map-wrap"
        data-attendance-location-map
        data-lat-input="{{ $latInputId }}"
        data-lng-input="{{ $lngInputId }}"
        data-radius-input="{{ $radiusInputId }}"
        @if($bufferInputSelector) data-buffer-input="{{ $bufferInputSelector }}" @endif
        @if($initialLat !== null) data-initial-lat="{{ $initialLat }}" @endif
        @if($initialLng !== null) data-initial-lng="{{ $initialLng }}" @endif
        data-initial-radius="{{ $initialRadius }}"
        data-existing-locations="{{ json_encode($existingJson) }}"
    >
        @if($showSearch || $showGpsButton)
            <div class="attendance-location-map-toolbar">
                @if($showSearch)
                    <div class="attendance-location-map-search">
                        <span class="attendance-location-map-search__icon" aria-hidden="true">
                            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                        </span>
                        <input
                            type="search"
                            data-map-search
                            class="attendance-location-map-search__input"
                            placeholder="{{ __('pages.settings.attendance_map_search_placeholder') }}"
                            data-not-found-message="{{ __('pages.settings.attendance_map_search_not_found') }}"
                            data-error-message="{{ __('pages.settings.attendance_map_search_error') }}"
                        >
                    </div>
                    <button type="button" data-map-search-btn class="btn-primary attendance-location-map-toolbar__btn">
                        {{ __('pages.settings.attendance_map_search') }}
                    </button>
                @endif
                @if($showGpsButton)
                    <button
                        type="button"
                        data-map-gps-btn
                        class="btn-secondary attendance-location-map-toolbar__btn attendance-location-map-toolbar__btn--gps"
                        data-unsupported-message="{{ __('pages.settings.attendance_map_gps_unsupported') }}"
                        data-error-message="{{ __('pages.settings.attendance_map_gps_error') }}"
                    >
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9.69 18.933l.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 00.281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 103 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 002.273 1.765 11.842 11.842 0 00.976.544l.062.029.018.008.006.003zM10 11.25a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" clip-rule="evenodd" />
                        </svg>
                        {{ __('pages.settings.attendance_map_gps') }}
                    </button>
                @endif
            </div>
        @endif

        <div class="attendance-location-map-frame">
            <div id="{{ $mapId }}" class="attendance-location-map" role="application" aria-label="{{ __('pages.settings.attendance_map_aria') }}"></div>
        </div>

        <div class="attendance-location-map-legend" aria-hidden="true">
            <span class="attendance-location-map-legend__item">
                <span class="attendance-location-map-legend__dot attendance-location-map-legend__dot--active"></span>
                {{ __('pages.settings.attendance_map_legend_active') }}
            </span>
            @if($hasBuffer)
                <span class="attendance-location-map-legend__item">
                    <span class="attendance-location-map-legend__dot attendance-location-map-legend__dot--buffer"></span>
                    {{ __('pages.settings.attendance_map_legend_buffer', ['meters' => (int) $bufferMeters]) }}
                </span>
            @endif
            @if($hasExisting)
                <span class="attendance-location-map-legend__item">
                    <span class="attendance-location-map-legend__dot attendance-location-map-legend__dot--existing"></span>
                    {{ __('pages.settings.attendance_map_legend_existing') }}
                </span>
            @endif
        </div>

        <p class="attendance-location-map-hint">{{ __('pages.settings.attendance_map_hint') }}</p>
    </div>
</div>
