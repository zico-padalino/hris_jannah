@php
    $mobile = $mobile ?? false;
    $linkClass = $mobile ? 'nav-link-mobile' : 'nav-link';
    $activeClass = $mobile ? 'nav-link-mobile--active' : 'nav-link--active';
    $inactiveClass = $mobile ? '' : 'hover:bg-teal-800';
    $user = auth()->user();
    $sidebar = $sidebar ?? app(\App\Services\SidebarService::class);
    $pendingTotal = $pendingLeaveApprovalCount ?? 0;
    $canApprove = $user->hasPermission(\App\Enums\Permission::LeaveApprove);
@endphp

@foreach($sidebar->navigationGroups($user) as $group)
    @if($group['collapsible'])
        @include('partials.sidebar-nav-group', [
            'group' => $group,
            'mobile' => $mobile,
            'linkClass' => $linkClass,
            'activeClass' => $activeClass,
            'inactiveClass' => $inactiveClass,
            'user' => $user,
            'sidebar' => $sidebar,
            'pendingTotal' => $pendingTotal,
            'canApprove' => $canApprove,
        ])
    @else
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
                'nested' => false,
            ])
        @endforeach
    @endif
@endforeach
