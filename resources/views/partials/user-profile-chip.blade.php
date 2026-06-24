@php
    $compact = $compact ?? false;
@endphp

<a
    href="{{ route('profile.edit') }}"
    @class([
        'user-profile-chip group',
        'user-profile-chip--compact' => $compact,
    ])
    title="{{ __('pages.profile.edit_title') }}"
    aria-label="{{ __('pages.profile.open_label') }}"
>
    @if(auth()->user()->hasProfilePhoto())
        <img
            src="{{ auth()->user()->profilePhotoUrl() }}"
            alt=""
            class="user-profile-chip__avatar"
        >
    @else
        <span class="user-profile-chip__avatar user-profile-chip__avatar--placeholder" aria-hidden="true">
            {{ auth()->user()->profileInitials() }}
        </span>
    @endif

    @if($compact)
        <span class="user-profile-chip__name truncate">{{ auth()->user()->name }}</span>
        <span class="user-profile-chip__badge">{{ __('pages.profile.open_label') }}</span>
    @else
        <span class="user-profile-chip__body min-w-0">
            <span class="user-profile-chip__name truncate">{{ auth()->user()->name }}</span>
            <span class="user-profile-chip__sub">
                <span class="user-profile-chip__role truncate">{{ auth()->user()->role->label() }}</span>
                <span class="user-profile-chip__badge">{{ __('pages.profile.open_label') }}</span>
            </span>
        </span>
    @endif
</a>
