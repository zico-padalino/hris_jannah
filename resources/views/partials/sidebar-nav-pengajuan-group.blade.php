@php
    use App\Enums\SidebarNavItem;

    $breakdown = $pendingLeaveApprovalBreakdown ?? ['cuti' => 0, 'izin' => 0, 'lembur' => 0];
    $subsections = [
        'cuti' => SidebarNavItem::SectionLeaveCuti,
        'izin' => SidebarNavItem::SectionLeaveIzin,
        'lembur' => SidebarNavItem::SectionLeaveLembur,
    ];
    $approvalItems = [
        'cuti' => SidebarNavItem::LeaveCutiApproval,
        'izin' => SidebarNavItem::LeaveIzinApproval,
        'lembur' => SidebarNavItem::LeaveLemburApproval,
    ];
    $canSeeAnyApproval = collect(SidebarNavItem::leaveApprovalItems())
        ->contains(fn (SidebarNavItem $item) => $sidebar->visible($user, $item));
    $showGroupBadge = $canSeeAnyApproval && $canApprove && ($pendingTotal ?? 0) > 0;
@endphp

<div
    class="sidebar-group sidebar-group--pengajuan sidebar-group--collapsed {{ $mobile ? 'sidebar-group--mobile' : '' }}"
    data-sidebar-group="{{ $group['id'] }}"
>
    <button
        type="button"
        class="sidebar-group__toggle"
        aria-expanded="false"
        aria-controls="sidebar-group-items-{{ $group['id'] }}"
    >
        <span class="sidebar-group__label">{{ $group['label'] }}</span>
        <span class="sidebar-group__meta">
            @if($showGroupBadge)
                @include('partials.count-badge', [
                    'count' => $pendingTotal,
                    'variant' => 'sidebar-module',
                    'label' => __('app.new'),
                    'pulse' => true,
                ])
            @endif
            <svg class="sidebar-group__chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </span>
    </button>

    <div id="sidebar-group-items-{{ $group['id'] }}" class="sidebar-group__items">
        @foreach($subsections as $category => $section)
            @php
                $subItems = array_values(array_filter(
                    $group['items'],
                    fn (array $entry) => ($entry['item'] ?? null) instanceof SidebarNavItem
                        && $entry['item']->leaveApprovalCategory() === $category
                        && ($entry['item']->isLeaveHistory()
                            || $entry['item']->isLeaveCreate()
                            || $entry['item']->isLeaveApproval())
                ));

                usort($subItems, function (array $a, array $b) {
                    $priority = fn (SidebarNavItem $item) => match (true) {
                        $item->isLeaveCreate() => 0,
                        $item->isLeaveHistory() => 1,
                        $item->isLeaveApproval() => 2,
                        default => 3,
                    };

                    return $priority($a['item']) <=> $priority($b['item']);
                });

                $subId = $group['id'].'-'.$category;
                $categoryCount = (int) ($breakdown[$category] ?? 0);
                $approvalItem = $approvalItems[$category];
                $showModuleBadge = $canApprove
                    && $categoryCount > 0
                    && $sidebar->visible($user, $approvalItem);
                $subgroupCollapsed = ! $showModuleBadge;
            @endphp

            @if($subItems !== [])
                <div
                    class="sidebar-group sidebar-subgroup sidebar-subgroup--module {{ $mobile ? 'sidebar-group--mobile' : '' }} {{ $subgroupCollapsed ? 'sidebar-group--collapsed' : '' }}"
                    data-sidebar-group="{{ $subId }}"
                >
                    <button
                        type="button"
                        class="sidebar-group__toggle sidebar-subgroup__toggle"
                        aria-expanded="{{ $subgroupCollapsed ? 'false' : 'true' }}"
                        aria-controls="sidebar-group-items-{{ $subId }}"
                    >
                        <span class="sidebar-group__label">{{ __($section->navLabelKey()) }}</span>
                        <span class="sidebar-group__meta">
                            @if($showModuleBadge)
                                @include('partials.count-badge', [
                                    'count' => $categoryCount,
                                    'variant' => 'sidebar-module',
                                    'label' => __('app.new'),
                                    'pulse' => true,
                                ])
                            @endif
                            <svg class="sidebar-group__chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </button>

                    <div id="sidebar-group-items-{{ $subId }}" class="sidebar-group__items sidebar-subgroup__items">
                        @foreach($subItems as $entry)
                            @include('partials.sidebar-nav-entry', [
                                'entry' => $entry,
                                'mobile' => $mobile,
                                'linkClass' => $linkClass,
                                'activeClass' => $activeClass,
                                'inactiveClass' => $inactiveClass,
                                'user' => $user,
                                'sidebar' => $sidebar,
                                'pendingTotal' => $pendingTotal,
                                'pendingLeaveApprovalBreakdown' => $breakdown,
                                'pendingPayrollTotal' => $pendingPayrollTotal,
                                'canApprove' => $canApprove,
                                'canApprovePayroll' => $canApprovePayroll,
                                'nested' => true,
                            ])
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        @foreach($group['items'] as $entry)
            @if(($entry['type'] ?? '') === 'custom_link')
                @include('partials.sidebar-nav-entry', [
                    'entry' => $entry,
                    'mobile' => $mobile,
                    'linkClass' => $linkClass,
                    'activeClass' => $activeClass,
                    'inactiveClass' => $inactiveClass,
                    'user' => $user,
                    'sidebar' => $sidebar,
                    'pendingTotal' => $pendingTotal,
                    'pendingLeaveApprovalBreakdown' => $breakdown,
                    'pendingPayrollTotal' => $pendingPayrollTotal,
                    'canApprove' => $canApprove,
                    'canApprovePayroll' => $canApprovePayroll,
                    'nested' => true,
                ])
            @endif
        @endforeach
    </div>
</div>
