@props([
    'active' => true,
    'activeLabel' => 'Aktif',
    'inactiveLabel' => 'Nonaktif',
])

<span @class([
    'app-status-badge',
    'app-status-badge--active' => $active,
    'app-status-badge--inactive' => ! $active,
])>
    <span class="app-status-badge__dot" aria-hidden="true"></span>
    <span>{{ $active ? $activeLabel : $inactiveLabel }}</span>
</span>
