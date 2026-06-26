function initThemeToggle() {
    const root = document.documentElement;
    const toggles = document.querySelectorAll('[data-theme-toggle]');

    function isDark() {
        return root.classList.contains('dark');
    }

    function applyTheme(dark) {
        root.classList.toggle('dark', dark);
        try {
            localStorage.setItem('theme', dark ? 'dark' : 'light');
        } catch (e) {}
        window.dispatchEvent(new CustomEvent('theme:changed', { detail: { dark } }));
    }

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            applyTheme(!isDark());
        });
    });
}

function initReadableTables() {
    document.querySelectorAll('table.table-readable').forEach((table) => {
        const headers = [...table.querySelectorAll('thead th')].map((th) => th.textContent.trim());

        table.querySelectorAll('tbody tr').forEach((row) => {
            [...row.children].forEach((cell, index) => {
                if (cell.tagName !== 'TD' || cell.hasAttribute('colspan')) {
                    return;
                }

                if (headers[index]) {
                    cell.dataset.label = headers[index];
                }
            });
        });
    });
}

function collapseMobileSidebarGroups() {
    document.querySelectorAll('.sidebar-group--mobile').forEach((group) => {
        const toggle = group.querySelector('.sidebar-group__toggle');

        group.classList.add('sidebar-group--collapsed');
        toggle?.setAttribute('aria-expanded', 'false');
    });
}

function initSidebarGroups() {
    const storageKey = 'sidebar_collapsed_groups';
    let collapsed = {};

    try {
        collapsed = JSON.parse(localStorage.getItem(storageKey) || '{}');
    } catch (e) {}

    document.querySelectorAll('[data-sidebar-group]').forEach((group) => {
        const groupId = group.dataset.sidebarGroup;
        const toggle = group.querySelector('.sidebar-group__toggle');
        const items = group.querySelector('.sidebar-group__items');
        const isMobileGroup = group.classList.contains('sidebar-group--mobile');

        if (!toggle || !items || !groupId) {
            return;
        }

        const hasActive = Boolean(items.querySelector('.nav-link--active, .nav-link-mobile--active'));

        function setCollapsed(isCollapsed, persist = false) {
            group.classList.toggle('sidebar-group--collapsed', isCollapsed);
            toggle.setAttribute('aria-expanded', String(!isCollapsed));

            if (persist && !isMobileGroup) {
                collapsed[groupId] = isCollapsed;

                try {
                    localStorage.setItem(storageKey, JSON.stringify(collapsed));
                } catch (e) {}
            }
        }

        if (isMobileGroup) {
            setCollapsed(true);

            toggle.addEventListener('click', () => {
                setCollapsed(!group.classList.contains('sidebar-group--collapsed'));
            });

            return;
        }

        setCollapsed(collapsed[groupId] === true && !hasActive);

        toggle.addEventListener('click', () => {
            setCollapsed(!group.classList.contains('sidebar-group--collapsed'), true);
        });
    });
}

function formatRupiahDigits(digits) {
    if (digits === '') {
        return '';
    }

    return Number(digits).toLocaleString('id-ID');
}

function parseRupiahDigits(value) {
    return String(value ?? '').replace(/\D/g, '');
}

function initRupiahInputs() {
    document.querySelectorAll('[data-rupiah-input]').forEach((input) => {
        input.value = formatRupiahDigits(parseRupiahDigits(input.value));

        input.addEventListener('input', () => {
            const digits = parseRupiahDigits(input.value);

            if (digits.length > 15) {
                input.value = formatRupiahDigits(digits.slice(0, 15));

                return;
            }

            input.value = formatRupiahDigits(digits);
        });
    });

    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.rupiahNormalize === '1') {
            return;
        }

        form.dataset.rupiahNormalize = '1';

        form.addEventListener('submit', () => {
            form.querySelectorAll('[data-rupiah-input]').forEach((input) => {
                input.value = parseRupiahDigits(input.value);
            });
        });
    });
}

function initLiveClock() {
    const clocks = document.querySelectorAll('[data-live-clock]');

    if (!clocks.length) {
        return;
    }

    function partValue(parts, type) {
        return parts.find((part) => part.type === type)?.value ?? '00';
    }

    function formatTime(timezone) {
        const formatter = new Intl.DateTimeFormat('en-GB', {
            timeZone: timezone,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });
        const parts = formatter.formatToParts(new Date());

        return `${partValue(parts, 'hour')}:${partValue(parts, 'minute')}:${partValue(parts, 'second')}`;
    }

    function tick() {
        clocks.forEach((clock) => {
            const timeEl = clock.querySelector('[data-clock-time]');

            if (!timeEl) {
                return;
            }

            const timezone = clock.dataset.timezone || 'Asia/Jakarta';
            timeEl.textContent = formatTime(timezone);
        });
    }

    tick();
    window.setInterval(tick, 1000);
}

function closeAllHeaderNotifications() {
    document.querySelectorAll('[data-header-notifications].header-notifications--open').forEach((menu) => {
        menu.classList.remove('header-notifications--open');
        menu.querySelector('[data-header-notifications-trigger]')?.setAttribute('aria-expanded', 'false');
        const panel = menu.querySelector('[data-header-notifications-panel]');

        if (panel) {
            panel.hidden = true;
        }
    });
}

function closeAllUserAccountMenus() {
    document.querySelectorAll('[data-user-account-menu].user-account-menu--open').forEach((menu) => {
        menu.classList.remove('user-account-menu--open');
        menu.querySelector('[data-user-account-menu-trigger]')?.setAttribute('aria-expanded', 'false');
        const panel = menu.querySelector('[data-user-account-menu-panel]');

        if (panel) {
            panel.hidden = true;
        }
    });
}

function initHeaderNotifications() {
    document.querySelectorAll('[data-header-notifications]').forEach((menu) => {
        const trigger = menu.querySelector('[data-header-notifications-trigger]');
        const panel = menu.querySelector('[data-header-notifications-panel]');

        if (!trigger || !panel) {
            return;
        }

        function close() {
            menu.classList.remove('header-notifications--open');
            trigger.setAttribute('aria-expanded', 'false');
            panel.hidden = true;
        }

        function open() {
            closeAllHeaderNotifications();
            closeAllUserAccountMenus();
            menu.classList.add('header-notifications--open');
            trigger.setAttribute('aria-expanded', 'true');
            panel.hidden = false;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();

            if (menu.classList.contains('header-notifications--open')) {
                close();
            } else {
                open();
            }
        });

        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target)) {
                close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
            }
        });
    });
}

function initUserAccountMenus() {
    document.querySelectorAll('[data-user-account-menu]').forEach((menu) => {
        const trigger = menu.querySelector('[data-user-account-menu-trigger]');
        const panel = menu.querySelector('[data-user-account-menu-panel]');

        if (!trigger || !panel) {
            return;
        }

        function close() {
            menu.classList.remove('user-account-menu--open');
            trigger.setAttribute('aria-expanded', 'false');
            panel.hidden = true;
        }

        function open() {
            closeAllUserAccountMenus();
            closeAllHeaderNotifications();

            menu.classList.add('user-account-menu--open');
            trigger.setAttribute('aria-expanded', 'true');
            panel.hidden = false;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();

            if (menu.classList.contains('user-account-menu--open')) {
                close();
            } else {
                open();
            }
        });

        document.addEventListener('click', (event) => {
            if (!menu.contains(event.target)) {
                close();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                close();
            }
        });
    });
}

function initMobileNav() {
    const toggle = document.getElementById('mobile-nav-toggle');
    const close = document.getElementById('mobile-nav-close');
    const overlay = document.getElementById('mobile-nav-overlay');
    const backdrop = document.getElementById('mobile-nav-backdrop');

    if (!overlay) {
        return;
    }

    function syncMobileHeaderOffset() {
        const header = document.querySelector('.app-header');

        if (!header) {
            return;
        }

        document.documentElement.style.setProperty('--mobile-header-offset', `${header.offsetHeight}px`);
    }

    function openMenu() {
        syncMobileHeaderOffset();
        overlay.classList.add('mobile-nav-overlay--open');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('mobile-nav-open');
        toggle?.setAttribute('aria-expanded', 'true');
        toggle?.setAttribute('aria-label', toggle.dataset.closeLabel || toggle.getAttribute('aria-label') || '');
        document.body.classList.add('overflow-hidden');
    }

    function closeMenu() {
        overlay.classList.remove('mobile-nav-overlay--open');
        overlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('mobile-nav-open');
        toggle?.setAttribute('aria-expanded', 'false');
        toggle?.setAttribute('aria-label', toggle.dataset.openLabel || toggle.getAttribute('aria-label') || '');
        document.body.classList.remove('overflow-hidden');
        collapseMobileSidebarGroups();
    }

    syncMobileHeaderOffset();
    window.addEventListener('resize', syncMobileHeaderOffset);

    if (toggle) {
        toggle.addEventListener('click', () => {
            if (toggle.getAttribute('aria-expanded') === 'true') {
                closeMenu();
            } else {
                openMenu();
            }
        });
    }

    close?.addEventListener('click', closeMenu);
    backdrop?.addEventListener('click', closeMenu);
    overlay.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && overlay.classList.contains('mobile-nav-overlay--open')) {
            closeMenu();
        }
    });
}

function initLeaveProofModal() {
    const modal = document.getElementById('leave-proof-modal');

    if (!modal) {
        return;
    }

    const image = document.getElementById('leave-proof-image');
    const pdfFrame = document.getElementById('leave-proof-pdf');
    const download = document.getElementById('leave-proof-download');
    const title = document.getElementById('leave-proof-title');
    const meta = document.getElementById('leave-proof-meta');

    function show(el) {
        el.classList.remove('hidden');
    }

    function hide(el) {
        el.classList.add('hidden');
    }

    function close() {
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');

        if (image) {
            image.removeAttribute('src');
            hide(image);
        }

        if (pdfFrame) {
            pdfFrame.removeAttribute('src');
            hide(pdfFrame);
        }
    }

    function open(trigger) {
        const url = trigger.dataset.proofUrl;
        const kind = trigger.dataset.proofKind || 'image';

        if (!url) {
            return;
        }

        if (title) {
            title.textContent = trigger.dataset.proofTitle || title.textContent;
        }

        if (meta) {
            meta.textContent = trigger.dataset.proofMeta || '';
        }

        if (download) {
            download.href = url;
        }

        if (kind === 'pdf' && pdfFrame) {
            hide(image);
            pdfFrame.src = url;
            show(pdfFrame);
        } else if (image) {
            hide(pdfFrame);
            image.src = url;
            show(image);
        }

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-leave-proof-trigger]');

        if (trigger) {
            event.preventDefault();
            open(trigger);

            return;
        }

        if (event.target.closest('[data-leave-proof-close]')) {
            event.preventDefault();
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            close();
        }
    });
}

function initPasswordFields() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const field = button.closest('.password-field');
        const input = field?.querySelector('input');

        if (!input) {
            return;
        }

        const showLabel = button.dataset.showLabel || 'Show password';
        const hideLabel = button.dataset.hideLabel || 'Hide password';

        button.addEventListener('click', () => {
            const isVisible = input.type === 'text';

            input.type = isVisible ? 'password' : 'text';
            button.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
            button.setAttribute('aria-label', isVisible ? showLabel : hideLabel);
            field.classList.toggle('password-field--visible', !isVisible);
        });
    });
}

function initScrollToTopOnLoad() {
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    function resetPageScroll() {
        window.scrollTo(0, 0);
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;

        const main = document.getElementById('app-main');

        if (main) {
            main.scrollTop = 0;
        }
    }

    resetPageScroll();

    window.addEventListener('pageshow', () => {
        resetPageScroll();
    });

    window.addEventListener('load', () => {
        resetPageScroll();
    });

    document.addEventListener('DOMContentLoaded', resetPageScroll);
}

initScrollToTopOnLoad();

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initReadableTables();
    initSidebarGroups();
    initRupiahInputs();
    initLiveClock();
    initHeaderNotifications();
    initUserAccountMenus();
    initMobileNav();
    initLeaveProofModal();
    initPasswordFields();
});
