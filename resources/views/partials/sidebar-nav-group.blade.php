@php
    $isLeaveSection = ($group['builtin'] ?? null) === 'section_leave';
    $showLeaveBadge = $isLeaveSection && $pendingTotal > 0 && $canApprove && $sidebar->visible($user, \App\Enums\SidebarNavItem::LeaveApproval);
@endphp

<div
    class="sidebar-group {{ $mobile ? 'sidebar-group--mobile' : '' }}"
    data-sidebar-group="{{ $group['id'] }}"
>
    <button
        type="button"
        class="sidebar-group__toggle"
        aria-expanded="true"
        aria-controls="sidebar-group-items-{{ $group['id'] }}"
    >
        <span class="sidebar-group__label">{{ $group['label'] }}</span>
        <span class="sidebar-group__meta">
            @if($showLeaveBadge)
                @include('partials.count-badge', ['count' => $pendingTotal, 'variant' => 'dot', 'pulse' => true])
            @endif
            <svg class="sidebar-group__chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    <div id="sidebar-group-items-{{ $group['id'] }}" class="sidebar-group__items">
        @foreach($group['items'] as $entry)
            @include('partials.sidebar-nav-entry', [
                'entry' => $entry,
                'mobile' => $mobile,
                'linkClass' => $linkClass,
                'activeClass' => $activeClass,
                'inactiveClass' => $inactiveClass,
                'user' => $user,
                'sidebar' => $sidebar,
                'pendingTotal' => $pendingTotal,
                'canApprove' => $canApprove,
                'nested' => true,
            ])
        @endforeach
    </div>
</div>
