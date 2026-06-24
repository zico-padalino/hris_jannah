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

function initLiveClock() {
    const clock = document.getElementById('header-live-clock');

    if (!clock) {
        return;
    }

    const timeEl = clock.querySelector('[data-clock-time]');
    const timezone = clock.dataset.timezone || 'Asia/Jakarta';

    if (!timeEl) {
        return;
    }

    const formatter = new Intl.DateTimeFormat('en-GB', {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });

    function partValue(parts, type) {
        return parts.find((part) => part.type === type)?.value ?? '00';
    }

    function tick() {
        const parts = formatter.formatToParts(new Date());
        timeEl.textContent = `${partValue(parts, 'hour')}:${partValue(parts, 'minute')}:${partValue(parts, 'second')}`;
    }

    tick();
    window.setInterval(tick, 1000);
}

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initReadableTables();
    initSidebarGroups();
    initLiveClock();
});
