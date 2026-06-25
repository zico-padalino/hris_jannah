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

        if (!toggle || !items || !groupId) {
            return;
        }

        const hasActive = Boolean(items.querySelector('.nav-link--active, .nav-link-mobile--active'));

        function setCollapsed(isCollapsed, persist = false) {
            group.classList.toggle('sidebar-group--collapsed', isCollapsed);
            toggle.setAttribute('aria-expanded', String(!isCollapsed));

            if (persist) {
                collapsed[groupId] = isCollapsed;

                try {
                    localStorage.setItem(storageKey, JSON.stringify(collapsed));
                } catch (e) {}
            }
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
            document.querySelectorAll('[data-user-account-menu].user-account-menu--open').forEach((other) => {
                if (other === menu) {
                    return;
                }

                other.classList.remove('user-account-menu--open');
                other.querySelector('[data-user-account-menu-trigger]')?.setAttribute('aria-expanded', 'false');
                const otherPanel = other.querySelector('[data-user-account-menu-panel]');

                if (otherPanel) {
                    otherPanel.hidden = true;
                }
            });

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

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initReadableTables();
    initSidebarGroups();
    initRupiahInputs();
    initLiveClock();
    initUserAccountMenus();
});
