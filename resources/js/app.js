const shell = document.querySelector('[data-dashboard-shell]');
const toasts = document.querySelectorAll('[data-toast]');
const clientTable = document.querySelector('[data-client-table]');
const profileImageInput = document.querySelector('[data-profile-image-input]');
const profileImagePreview = document.querySelector('[data-profile-image-preview]');

if (shell) {
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const colorInput = document.querySelector('[data-theme-color-input]');
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    const groups = document.querySelectorAll('[data-sidebar-group]');
    const subLinks = document.querySelectorAll('[data-sidebar-sub-link]');

    const isMobile = () => window.matchMedia('(max-width: 900px)').matches;

    const closeMobileSidebar = () => {
        shell.classList.remove('sidebar-mobile-open');
    };

    sidebarToggle?.addEventListener('click', () => {
        if (isMobile()) {
            shell.classList.toggle('sidebar-mobile-open');
            return;
        }

        shell.classList.toggle('sidebar-collapsed');
        localStorage.setItem('dashboard-sidebar-collapsed', shell.classList.contains('sidebar-collapsed') ? '1' : '0');
    });

    if (localStorage.getItem('dashboard-sidebar-collapsed') === '1' && ! isMobile()) {
        shell.classList.add('sidebar-collapsed');
    }

    backdrop?.addEventListener('click', closeMobileSidebar);

    window.addEventListener('resize', () => {
        if (! isMobile()) {
            closeMobileSidebar();
        }
    });

    groups.forEach((group) => {
        const toggle = group.querySelector('[data-submenu-toggle]');

        toggle?.addEventListener('click', () => {
            groups.forEach((item) => {
                if (item !== group) {
                    item.classList.remove('is-open');
                    item.querySelector('[data-submenu-toggle]')?.setAttribute('aria-expanded', 'false');
                }
            });

            const isOpen = group.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    subLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            if (link.getAttribute('href') !== '#') {
                return;
            }

            event.preventDefault();

            subLinks.forEach((item) => item.classList.remove('is-active'));
            link.classList.add('is-active');

            groups.forEach((group) => {
                const isCurrentGroup = group.contains(link);
                group.classList.toggle('is-active', isCurrentGroup);
                group.classList.toggle('is-open', isCurrentGroup);
                group.querySelector('[data-submenu-toggle]')?.setAttribute('aria-expanded', isCurrentGroup ? 'true' : 'false');
            });
        });
    });

    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('[data-dropdown-toggle]');

        toggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            dropdowns.forEach((item) => {
                if (item !== dropdown) {
                    item.classList.remove('is-open');
                    item.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
                }
            });

            const isOpen = dropdown.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    document.addEventListener('click', () => {
        dropdowns.forEach((dropdown) => {
            dropdown.classList.remove('is-open');
            dropdown.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    });

    themeToggle?.addEventListener('click', () => {
        const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = nextTheme;
        localStorage.setItem('dashboard-theme', nextTheme);
    });

    colorInput?.addEventListener('input', (event) => {
        document.documentElement.style.setProperty('--primary', event.target.value);
    });

}

toasts.forEach((toast) => {
    const close = () => {
        toast.classList.add('is-hiding');
        window.setTimeout(() => toast.remove(), 220);
    };

    toast.querySelector('[data-toast-close]')?.addEventListener('click', close);
    window.setTimeout(close, 4500);
});

if (clientTable) {
    const rows = Array.from(clientTable.querySelectorAll('[data-client-body] tr'));
    const search = clientTable.querySelector('[data-client-search]');
    const status = clientTable.querySelector('[data-client-status]');
    const plan = clientTable.querySelector('[data-client-plan]');
    const perPage = clientTable.querySelector('[data-client-per-page]');
    const summary = clientTable.querySelector('[data-client-summary]');
    const current = clientTable.querySelector('[data-page-current]');
    const first = clientTable.querySelector('[data-page-first]');
    const prev = clientTable.querySelector('[data-page-prev]');
    const next = clientTable.querySelector('[data-page-next]');
    const last = clientTable.querySelector('[data-page-last]');
    const sortButtons = clientTable.querySelectorAll('[data-sort]');
    let page = 1;
    let sortKey = 'joined';
    let sortDirection = 'desc';

    const getFilteredRows = () => {
        const query = search.value.trim().toLowerCase();

        return rows.filter((row) => {
            const rowText = `${row.dataset.name} ${row.dataset.company} ${row.dataset.email}`.toLowerCase();
            const matchesSearch = !query || rowText.includes(query);
            const matchesStatus = !status.value || row.dataset.status === status.value;
            const matchesPlan = !plan.value || row.dataset.plan === plan.value;

            return matchesSearch && matchesStatus && matchesPlan;
        }).sort((a, b) => {
            const left = ['value'].includes(sortKey) ? Number(a.dataset[sortKey]) : a.dataset[sortKey];
            const right = ['value'].includes(sortKey) ? Number(b.dataset[sortKey]) : b.dataset[sortKey];

            if (left < right) return sortDirection === 'asc' ? -1 : 1;
            if (left > right) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    };

    const render = () => {
        const filtered = getFilteredRows();
        const limit = Number(perPage.value);
        const totalPages = Math.max(1, Math.ceil(filtered.length / limit));
        page = Math.min(page, totalPages);
        const start = (page - 1) * limit;
        const visible = filtered.slice(start, start + limit);

        rows.forEach((row) => row.hidden = true);
        visible.forEach((row) => row.hidden = false);

        summary.textContent = filtered.length
            ? `Showing ${start + 1}-${start + visible.length} of ${filtered.length} clients`
            : 'No clients found';
        current.textContent = `${page} / ${totalPages}`;

        first.disabled = page === 1;
        prev.disabled = page === 1;
        next.disabled = page === totalPages;
        last.disabled = page === totalPages;
    };

    [search, status, plan, perPage].forEach((input) => {
        input.addEventListener('input', () => {
            page = 1;
            render();
        });
    });

    sortButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const nextKey = button.dataset.sort;
            sortDirection = sortKey === nextKey && sortDirection === 'asc' ? 'desc' : 'asc';
            sortKey = nextKey;
            sortButtons.forEach((item) => item.classList.remove('is-sorted'));
            button.classList.add('is-sorted');
            render();
        });
    });

    first.addEventListener('click', () => { page = 1; render(); });
    prev.addEventListener('click', () => { page -= 1; render(); });
    next.addEventListener('click', () => { page += 1; render(); });
    last.addEventListener('click', () => {
        page = Math.ceil(getFilteredRows().length / Number(perPage.value));
        render();
    });

    render();
}

profileImageInput?.addEventListener('change', () => {
    const file = profileImageInput.files?.[0];

    if (!file || !file.type.startsWith('image/') || !profileImagePreview) {
        return;
    }

    const image = document.createElement('img');
    image.src = URL.createObjectURL(file);
    image.alt = 'Selected profile image';
    image.onload = () => URL.revokeObjectURL(image.src);

    profileImagePreview.replaceChildren(image);
});
