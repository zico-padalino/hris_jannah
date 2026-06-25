<div class="user-account-menu" data-user-account-menu>
    <button
        type="button"
        class="user-account-menu__trigger"
        data-user-account-menu-trigger
        aria-expanded="false"
        aria-haspopup="menu"
        aria-label="{{ __('app.account_menu') }}"
    >
        @if(auth()->user()->hasProfilePhoto())
            <img
                src="{{ auth()->user()->profilePhotoUrl() }}"
                alt=""
                class="user-account-menu__avatar"
            >
        @else
            <span class="user-account-menu__avatar user-account-menu__avatar--placeholder" aria-hidden="true">
                {{ auth()->user()->profileInitials() }}
            </span>
        @endif

        <span class="user-account-menu__label min-w-0">
            <span class="user-account-menu__name truncate">{{ auth()->user()->name }}</span>
            <span class="user-account-menu__role truncate">{{ auth()->user()->role->label() }}</span>
        </span>

        <svg class="user-account-menu__chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div class="user-account-menu__panel" data-user-account-menu-panel role="menu" hidden>
        <div class="user-account-menu__header">
            <p class="user-account-menu__header-name">{{ auth()->user()->name }}</p>
            <p class="user-account-menu__header-role">{{ auth()->user()->role->label() }}</p>
        </div>

        <a href="{{ route('profile.edit') }}" class="user-account-menu__item" role="menuitem">
            <svg class="user-account-menu__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
            {{ __('pages.profile.open_label') }}
        </a>

        <form method="POST" action="{{ route('logout') }}" class="user-account-menu__form">
            @csrf
            <button type="submit" class="user-account-menu__item user-account-menu__item--danger" role="menuitem">
                <svg class="user-account-menu__item-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                </svg>
                {{ __('app.logout') }}
            </button>
        </form>
    </div>
</div>
