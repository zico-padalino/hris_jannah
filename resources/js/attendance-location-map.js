import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const campfireMarkerIcon = L.divIcon({
    className: 'attendance-location-marker',
    html: '<span class="attendance-location-marker__pin" aria-hidden="true"></span>',
    iconSize: [30, 38],
    iconAnchor: [15, 38],
    popupAnchor: [0, -34],
});

const TILE_CONFIG = {
    light: {
        url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    },
    dark: {
        url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    },
};

function isDarkTheme() {
    return document.documentElement.classList.contains('dark');
}

const DEFAULT_CENTER = [-6.914744, 107.60981];
const DEFAULT_ZOOM = 15;

function parseNumber(value, fallback = null) {
    const parsed = Number.parseFloat(value);
    return Number.isFinite(parsed) ? parsed : fallback;
}

function readExistingLocations(root) {
    try {
        const parsed = JSON.parse(root.dataset.existingLocations || '[]');
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function fitMapToFeatures(map, layers) {
    const valid = layers.filter(Boolean);
    if (valid.length === 0) {
        map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
        return;
    }

    map.fitBounds(L.featureGroup(valid).getBounds().pad(0.2));
}

export function initAttendanceLocationMap(root) {
    if (!root || root.dataset.mapInitialized === '1') {
        return null;
    }

    const mapElement = root.querySelector('.attendance-location-map');
    if (!mapElement) {
        return null;
    }

    root.dataset.mapInitialized = '1';

    const latInput = document.getElementById(root.dataset.latInput || '');
    const lngInput = document.getElementById(root.dataset.lngInput || '');
    const radiusInput = document.getElementById(root.dataset.radiusInput || '');
    const bufferInput = root.dataset.bufferInput
        ? document.querySelector(root.dataset.bufferInput)
        : null;
    const searchInput = root.querySelector('[data-map-search]');
    const searchBtn = root.querySelector('[data-map-search-btn]');
    const gpsBtn = root.querySelector('[data-map-gps-btn]');

    const initialLat = parseNumber(root.dataset.initialLat ?? latInput?.value, DEFAULT_CENTER[0]);
    const initialLng = parseNumber(root.dataset.initialLng ?? lngInput?.value, DEFAULT_CENTER[1]);
    const initialRadius = parseNumber(root.dataset.initialRadius ?? radiusInput?.value, 100);

    const map = L.map(mapElement, { zoomControl: true }).setView([initialLat, initialLng], DEFAULT_ZOOM);

    let tileLayer = null;

    function applyMapTheme() {
        const theme = isDarkTheme() ? 'dark' : 'light';
        const config = TILE_CONFIG[theme];

        if (tileLayer) {
            map.removeLayer(tileLayer);
        }

        tileLayer = L.tileLayer(config.url, {
            attribution: config.attribution,
            maxZoom: 19,
        }).addTo(map);

        mapElement.classList.toggle('attendance-location-map--dark', theme === 'dark');
    }

    applyMapTheme();

    const marker = L.marker([initialLat, initialLng], {
        draggable: true,
        icon: campfireMarkerIcon,
    }).addTo(map);
    const radiusCircle = L.circle([initialLat, initialLng], {
        radius: initialRadius,
        color: '#EC6014',
        fillColor: '#F88F23',
        fillOpacity: 0.18,
        weight: 2.5,
    }).addTo(map);

    let bufferCircle = null;
    const existingLayers = [];

    readExistingLocations(root).forEach((location) => {
        const lat = parseNumber(location.latitude);
        const lng = parseNumber(location.longitude);
        const radius = parseNumber(location.radius_meters, 100);

        if (lat === null || lng === null) {
            return;
        }

        const circle = L.circle([lat, lng], {
            radius,
            color: location.is_active === false ? '#94a3b8' : '#c8510f',
            fillColor: location.is_active === false ? '#cbd5e1' : '#FBB931',
            fillOpacity: 0.1,
            weight: 2,
            dashArray: location.is_active === false ? '6 4' : '4 4',
        }).addTo(map);

        circle.bindPopup(`<strong>${location.name ?? 'Lokasi'}</strong><br>${radius} m`);
        existingLayers.push(circle);
    });

    function syncInputs(lat, lng) {
        if (latInput) {
            latInput.value = lat.toFixed(7);
            latInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (lngInput) {
            lngInput.value = lng.toFixed(7);
            lngInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    function updateMarkerPosition(lat, lng, pan = false) {
        marker.setLatLng([lat, lng]);
        radiusCircle.setLatLng([lat, lng]);
        if (bufferCircle) {
            bufferCircle.setLatLng([lat, lng]);
        }
        if (pan) {
            map.panTo([lat, lng]);
        }
        syncInputs(lat, lng);
    }

    function updateRadius(radius) {
        const safeRadius = Math.max(10, radius);
        radiusCircle.setRadius(safeRadius);
        updateBufferRing();
    }

    function updateBufferRing() {
        const buffer = parseNumber(bufferInput?.value, 0);
        const latLng = marker.getLatLng();
        const radius = parseNumber(radiusInput?.value, initialRadius);

        if (buffer <= 0) {
            if (bufferCircle) {
                map.removeLayer(bufferCircle);
                bufferCircle = null;
            }
            return;
        }

        const options = {
            radius: radius + buffer,
            color: '#9a4210',
            fillColor: '#FFE3B3',
            fillOpacity: 0.12,
            weight: 1.5,
            dashArray: '8 6',
        };

        if (bufferCircle) {
            bufferCircle.setLatLng(latLng);
            bufferCircle.setRadius(radius + buffer);
        } else {
            bufferCircle = L.circle(latLng, options).addTo(map);
        }
    }

    marker.on('dragend', () => {
        const { lat, lng } = marker.getLatLng();
        updateMarkerPosition(lat, lng);
    });

    map.on('click', (event) => {
        updateMarkerPosition(event.latlng.lat, event.latlng.lng);
    });

    radiusInput?.addEventListener('input', () => {
        updateRadius(parseNumber(radiusInput.value, initialRadius));
    });

    bufferInput?.addEventListener('input', updateBufferRing);

    latInput?.addEventListener('change', () => {
        const lat = parseNumber(latInput.value);
        const lng = parseNumber(lngInput?.value);
        if (lat !== null && lng !== null) {
            updateMarkerPosition(lat, lng, true);
            updateRadius(parseNumber(radiusInput?.value, initialRadius));
        }
    });

    lngInput?.addEventListener('change', () => latInput?.dispatchEvent(new Event('change')));

    gpsBtn?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            window.alert(gpsBtn.dataset.unsupportedMessage || 'Browser tidak mendukung geolocation.');
            return;
        }

        gpsBtn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                updateMarkerPosition(pos.coords.latitude, pos.coords.longitude, true);
                gpsBtn.disabled = false;
            },
            () => {
                window.alert(gpsBtn.dataset.errorMessage || 'Gagal mengambil lokasi. Izinkan akses GPS.');
                gpsBtn.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 15000 },
        );
    });

    async function runSearch(query) {
        const trimmed = query.trim();
        if (trimmed.length < 3) {
            return;
        }

        const url = new URL('https://nominatim.openstreetmap.org/search');
        url.searchParams.set('format', 'json');
        url.searchParams.set('limit', '5');
        url.searchParams.set('q', trimmed);
        url.searchParams.set('countrycodes', 'id');

        try {
            const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                return;
            }

            const results = await response.json();
            if (!Array.isArray(results) || results.length === 0) {
                window.alert(searchInput?.dataset.notFoundMessage || 'Alamat tidak ditemukan.');
                return;
            }

            const first = results[0];
            updateMarkerPosition(parseNumber(first.lat, DEFAULT_CENTER[0]), parseNumber(first.lon, DEFAULT_CENTER[1]), true);
            map.setZoom(Math.max(map.getZoom(), 16));
        } catch {
            window.alert(searchInput?.dataset.errorMessage || 'Gagal mencari alamat.');
        }
    }

    searchBtn?.addEventListener('click', () => {
        if (searchInput) {
            runSearch(searchInput.value);
        }
    });

    searchInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            runSearch(searchInput.value);
        }
    });

    updateRadius(initialRadius);
    updateBufferRing();
    fitMapToFeatures(map, [radiusCircle, marker, ...existingLayers]);

    const themeObserver = new MutationObserver(() => {
        applyMapTheme();
        window.setTimeout(() => map.invalidateSize(), 100);
    });

    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });

    window.setTimeout(() => map.invalidateSize(), 150);

    return {
        setPosition(lat, lng, pan = true) {
            updateMarkerPosition(lat, lng, pan);
        },
        setRadius(radius) {
            if (radiusInput) {
                radiusInput.value = String(radius);
            }
            updateRadius(radius);
        },
        invalidateSize() {
            map.invalidateSize();
        },
        destroy() {
            themeObserver.disconnect();
            map.remove();
        },
    };
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-attendance-location-map]').forEach((root) => {
        initAttendanceLocationMap(root);
    });
});
