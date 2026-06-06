const shell = document.querySelector('[data-dashboard-shell]');
const toasts = document.querySelectorAll('[data-toast]');
const clientTable = document.querySelector('[data-client-table]');
const profileImageInput = document.querySelector('[data-profile-image-input]');
const profileImagePreview = document.querySelector('[data-profile-image-preview]');
const forms = document.querySelectorAll('form');
const listingFilters = document.querySelectorAll('[data-listing-filter]');
const tooltipTargets = document.querySelectorAll('[data-tooltip]');
const formControls = document.querySelectorAll(
    '.form-field input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), .form-field select, .form-field textarea'
);
const nativeDateInputs = document.querySelectorAll('input[type="date"]');
const nativeSelects = document.querySelectorAll('select:not([multiple])');

const formatDateValue = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const parseDateValue = (value) => {
    if (!value) {
        return null;
    }

    const [year, month, day] = value.split('-').map(Number);

    if (!year || !month || !day) {
        return null;
    }

    return new Date(year, month - 1, day);
};

const formatDisplayDate = (value) => {
    const date = parseDateValue(value);

    if (!date) {
        return 'Select date';
    }

    return date.toLocaleDateString(undefined, {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const clearFieldValidation = (control) => {
    const field = control.closest('.form-field');

    if (!field || !field.classList.contains('has-error')) {
        return;
    }

    field.classList.remove('has-error');
    control.removeAttribute('aria-invalid');

    field.querySelectorAll('[data-validation-message]').forEach((message) => {
        message.hidden = true;
    });
};

formControls.forEach((control) => {
    const field = control.closest('.form-field');
    const messages = field ? field.querySelectorAll(':scope > small') : [];

    if (messages.length) {
        field.classList.add('has-error');
        control.setAttribute('aria-invalid', 'true');
        messages.forEach((message) => {
            message.dataset.validationMessage = 'true';
        });
    }

    ['input', 'change'].forEach((eventName) => {
        control.addEventListener(eventName, () => clearFieldValidation(control));
    });
});

nativeSelects.forEach((select) => {
    if (select.dataset.searchableSelect === 'ready') {
        return;
    }

    const wrapper = document.createElement('div');
    const trigger = document.createElement('button');
    const panel = document.createElement('div');
    const search = document.createElement('input');
    const list = document.createElement('div');
    const empty = document.createElement('div');

    const getSelectedOption = () => select.selectedOptions[0] || select.options[0];
    const syncLabel = () => {
        const selected = getSelectedOption();
        trigger.textContent = selected ? selected.textContent.trim() : 'Select option';
        trigger.classList.toggle('is-placeholder', !select.value);
    };

    const close = () => {
        wrapper.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    };

    const renderOptions = () => {
        const query = search.value.trim().toLowerCase();
        const options = Array.from(select.options).filter((option) => {
            return !query || option.textContent.toLowerCase().includes(query);
        });

        list.replaceChildren();

        options.forEach((option) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'searchable-select-option';
            item.textContent = option.textContent.trim();
            item.dataset.value = option.value;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', option.selected ? 'true' : 'false');

            item.addEventListener('click', () => {
                select.value = option.value;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
                syncLabel();
                close();
            });

            list.appendChild(item);
        });

        empty.hidden = options.length > 0;
    };

    wrapper.className = 'searchable-select';
    trigger.type = 'button';
    trigger.className = 'searchable-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    panel.className = 'searchable-select-panel';
    search.className = 'searchable-select-search';
    search.type = 'search';
    search.placeholder = 'Search options';
    list.className = 'searchable-select-list';
    list.setAttribute('role', 'listbox');
    empty.className = 'searchable-select-empty';
    empty.textContent = 'No options found';
    empty.hidden = true;

    select.dataset.searchableSelect = 'ready';
    select.classList.add('native-select');
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    panel.append(search, list, empty);
    wrapper.append(trigger, panel);

    trigger.addEventListener('click', (event) => {
        event.stopPropagation();
        document.querySelectorAll('.searchable-select.is-open').forEach((item) => {
            if (item !== wrapper) {
                item.classList.remove('is-open');
                item.querySelector('.searchable-select-trigger')?.setAttribute('aria-expanded', 'false');
            }
        });
        wrapper.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded', wrapper.classList.contains('is-open') ? 'true' : 'false');
        search.value = '';
        renderOptions();

        if (wrapper.classList.contains('is-open')) {
            window.setTimeout(() => search.focus(), 0);
        }
    });

    search.addEventListener('input', renderOptions);
    search.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
            trigger.focus();
        }
    });

    select.addEventListener('change', syncLabel);
    syncLabel();
    renderOptions();
});

nativeDateInputs.forEach((input) => {
    if (input.dataset.datePicker === 'ready') {
        return;
    }

    const wrapper = document.createElement('div');
    const trigger = document.createElement('button');
    const panel = document.createElement('div');
    const heading = document.createElement('div');
    const prev = document.createElement('button');
    const title = document.createElement('strong');
    const next = document.createElement('button');
    const weekdays = document.createElement('div');
    const grid = document.createElement('div');
    let visibleDate = parseDateValue(input.value) || new Date();

    const close = () => {
        wrapper.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    };

    const syncLabel = () => {
        trigger.textContent = formatDisplayDate(input.value);
        trigger.classList.toggle('is-placeholder', !input.value);
    };

    const renderCalendar = () => {
        const year = visibleDate.getFullYear();
        const month = visibleDate.getMonth();
        const selectedValue = input.value;
        const firstDay = new Date(year, month, 1);
        const start = new Date(year, month, 1 - firstDay.getDay());

        title.textContent = visibleDate.toLocaleDateString(undefined, {
            month: 'long',
            year: 'numeric',
        });
        grid.replaceChildren();

        Array.from({ length: 42 }).forEach((_, index) => {
            const date = new Date(start);
            date.setDate(start.getDate() + index);

            const value = formatDateValue(date);
            const day = document.createElement('button');
            day.type = 'button';
            day.className = 'date-picker-day';
            day.textContent = date.getDate();
            day.classList.toggle('is-muted', date.getMonth() !== month);
            day.classList.toggle('is-selected', value === selectedValue);

            day.addEventListener('click', () => {
                input.value = value;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                visibleDate = date;
                syncLabel();
                renderCalendar();
                close();
            });

            grid.appendChild(day);
        });
    };

    wrapper.className = 'date-picker';
    trigger.type = 'button';
    trigger.className = 'date-picker-trigger';
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-expanded', 'false');
    panel.className = 'date-picker-panel';
    heading.className = 'date-picker-heading';
    prev.type = 'button';
    prev.className = 'date-picker-nav';
    prev.textContent = '<';
    next.type = 'button';
    next.className = 'date-picker-nav';
    next.textContent = '>';
    weekdays.className = 'date-picker-weekdays';
    grid.className = 'date-picker-grid';

    ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach((day) => {
        const item = document.createElement('span');
        item.textContent = day;
        weekdays.appendChild(item);
    });

    input.dataset.datePicker = 'ready';
    input.classList.add('native-date-input');
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);
    heading.append(prev, title, next);
    panel.append(heading, weekdays, grid);
    wrapper.append(trigger, panel);

    trigger.addEventListener('click', (event) => {
        event.stopPropagation();
        document.querySelectorAll('.date-picker.is-open').forEach((item) => {
            if (item !== wrapper) {
                item.classList.remove('is-open');
                item.querySelector('.date-picker-trigger')?.setAttribute('aria-expanded', 'false');
            }
        });
        wrapper.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded', wrapper.classList.contains('is-open') ? 'true' : 'false');
        visibleDate = parseDateValue(input.value) || visibleDate || new Date();
        renderCalendar();
    });

    prev.addEventListener('click', () => {
        visibleDate = new Date(visibleDate.getFullYear(), visibleDate.getMonth() - 1, 1);
        renderCalendar();
    });

    next.addEventListener('click', () => {
        visibleDate = new Date(visibleDate.getFullYear(), visibleDate.getMonth() + 1, 1);
        renderCalendar();
    });

    input.addEventListener('change', () => {
        visibleDate = parseDateValue(input.value) || visibleDate;
        syncLabel();
        renderCalendar();
    });

    syncLabel();
    renderCalendar();
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('.searchable-select.is-open, .date-picker.is-open').forEach((item) => {
        if (!item.contains(event.target)) {
            item.classList.remove('is-open');
            item.querySelector('[aria-expanded="true"]')?.setAttribute('aria-expanded', 'false');
        }
    });
});

if (tooltipTargets.length) {
    const tooltip = document.createElement('div');
    let activeTooltipTarget = null;

    tooltip.className = 'dashboard-tooltip';
    tooltip.setAttribute('role', 'tooltip');
    tooltip.hidden = true;
    document.body.appendChild(tooltip);

    const positionTooltip = () => {
        if (!activeTooltipTarget || tooltip.hidden) {
            return;
        }

        const targetRect = activeTooltipTarget.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        const gap = 10;
        const viewportPadding = 12;
        const preferredTop = targetRect.top - tooltipRect.height - gap;
        const fallbackTop = targetRect.bottom + gap;
        const left = Math.min(
            Math.max(targetRect.left + (targetRect.width / 2) - (tooltipRect.width / 2), viewportPadding),
            window.innerWidth - tooltipRect.width - viewportPadding
        );
        const top = preferredTop >= viewportPadding
            ? preferredTop
            : Math.min(fallbackTop, window.innerHeight - tooltipRect.height - viewportPadding);

        tooltip.style.setProperty('--tooltip-x', `${Math.round(left)}px`);
        tooltip.style.setProperty('--tooltip-y', `${Math.round(Math.max(top, viewportPadding))}px`);
    };

    const showTooltip = (target) => {
        const label = target.dataset.tooltip?.trim();

        if (!label || target.disabled) {
            return;
        }

        activeTooltipTarget = target;
        tooltip.textContent = label;
        tooltip.hidden = false;
        tooltip.classList.remove('is-visible');
        positionTooltip();
        requestAnimationFrame(() => {
            if (activeTooltipTarget === target) {
                tooltip.classList.add('is-visible');
            }
        });
    };

    const hideTooltip = (target = activeTooltipTarget) => {
        if (target !== activeTooltipTarget) {
            return;
        }

        activeTooltipTarget = null;
        tooltip.classList.remove('is-visible');
        window.setTimeout(() => {
            if (!activeTooltipTarget) {
                tooltip.hidden = true;
            }
        }, 140);
    };

    tooltipTargets.forEach((target) => {
        target.addEventListener('mouseenter', () => showTooltip(target));
        target.addEventListener('mouseleave', () => hideTooltip(target));
        target.addEventListener('focus', () => showTooltip(target));
        target.addEventListener('blur', () => hideTooltip(target));
        target.addEventListener('click', () => hideTooltip(target));
    });

    window.addEventListener('scroll', positionTooltip, true);
    window.addEventListener('resize', () => hideTooltip());
}

listingFilters.forEach((filter) => {
    const toggle = filter.querySelector('[data-filter-toggle]');
    const panel = filter.querySelector('[data-filter-panel]');

    if (!toggle || !panel) {
        return;
    }

    const setOpen = (isOpen) => {
        filter.classList.toggle('is-open', isOpen);
        panel.hidden = !isOpen;
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Hide filters' : 'Show filters');
    };

    setOpen(filter.classList.contains('is-open') || !panel.hidden);

    toggle.addEventListener('click', () => {
        setOpen(!filter.classList.contains('is-open'));
    });
});

forms.forEach((form) => {
    form.addEventListener('submit', () => {
        if (form.dataset.submitting === 'true') {
            return;
        }

        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

        form.dataset.submitting = 'true';

        if (!submitButton) {
            return;
        }

        submitButton.disabled = true;
        submitButton.classList.add('is-loading');
        submitButton.setAttribute('aria-busy', 'true');

        if (submitButton.tagName !== 'BUTTON') {
            return;
        }

        const label = submitButton.textContent.trim() || 'Please wait';
        const loader = document.createElement('span');

        loader.className = 'submit-loader';
        loader.setAttribute('aria-hidden', 'true');

        if (submitButton.classList.contains('action-icon-btn')) {
            submitButton.replaceChildren(loader);
            submitButton.setAttribute('aria-label', submitButton.getAttribute('aria-label') || label);
            return;
        }

        const text = document.createElement('span');
        text.textContent = label;

        submitButton.replaceChildren(loader, text);
    });
});

if (shell) {
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const colorInput = document.querySelector('[data-theme-color-input]');
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    const groups = document.querySelectorAll('[data-sidebar-group]');
    const leafLinks = document.querySelectorAll('[data-sidebar-leaf]');
    const subLinks = document.querySelectorAll('[data-sidebar-sub-link]');

    const isMobile = () => window.matchMedia('(max-width: 900px)').matches;

    const closeMobileSidebar = () => {
        shell.classList.remove('sidebar-mobile-open');
    };

    const setSidebarGroupOpen = (group, isOpen) => {
        group.classList.toggle('is-open', isOpen);
        group.querySelector('[data-submenu-toggle]')?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    };

    const closeSidebarSubmenus = (preserveActive = false) => {
        const isCollapsedDesktop = shell.classList.contains('sidebar-collapsed') && ! isMobile();

        groups.forEach((group) => {
            if (preserveActive && group.classList.contains('is-active') && ! isCollapsedDesktop) {
                setSidebarGroupOpen(group, true);
                return;
            }

            setSidebarGroupOpen(group, false);
        });
    };

    const syncActiveSidebarGroup = () => {
        const isCollapsedDesktop = shell.classList.contains('sidebar-collapsed') && ! isMobile();

        groups.forEach((group) => {
            setSidebarGroupOpen(group, group.classList.contains('is-active') && ! isCollapsedDesktop);
        });
    };

    sidebarToggle?.addEventListener('click', () => {
        if (isMobile()) {
            shell.classList.toggle('sidebar-mobile-open');
            return;
        }

        shell.classList.toggle('sidebar-collapsed');
        localStorage.setItem('dashboard-sidebar-collapsed', shell.classList.contains('sidebar-collapsed') ? '1' : '0');
        syncActiveSidebarGroup();
    });

    if (localStorage.getItem('dashboard-sidebar-collapsed') === '1' && ! isMobile()) {
        shell.classList.add('sidebar-collapsed');
    }

    syncActiveSidebarGroup();

    backdrop?.addEventListener('click', closeMobileSidebar);

    window.addEventListener('resize', () => {
        if (! isMobile()) {
            closeMobileSidebar();
        }

        syncActiveSidebarGroup();
    });

    groups.forEach((group) => {
        const toggle = group.querySelector('[data-submenu-toggle]');

        group.addEventListener('mouseenter', () => {
            if (!shell.classList.contains('sidebar-collapsed') || isMobile()) {
                return;
            }

            groups.forEach((item) => {
                if (item !== group) {
                    setSidebarGroupOpen(item, false);
                }
            });

            setSidebarGroupOpen(group, true);
        });

        toggle?.addEventListener('click', () => {
            groups.forEach((item) => {
                if (item !== group) {
                    setSidebarGroupOpen(item, false);
                }
            });

            const isOpen = group.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    leafLinks.forEach((link) => {
        link.addEventListener('mouseenter', () => {
            if (!shell.classList.contains('sidebar-collapsed') || isMobile()) {
                return;
            }

            closeSidebarSubmenus();
        });

        link.addEventListener('click', closeSidebarSubmenus);
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
                setSidebarGroupOpen(group, isCurrentGroup);
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

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-sidebar-group], [data-sidebar-leaf]')) {
            closeSidebarSubmenus(true);
        }

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
    const rows = Array.from(clientTable.querySelectorAll('[data-client-body] tr:not([data-client-empty])'));
    const search = clientTable.querySelector('[data-client-search]');
    const status = clientTable.querySelector('[data-client-status]');
    const plan = clientTable.querySelector('[data-client-plan]');
    const apply = clientTable.querySelector('[data-client-apply]');
    const reset = clientTable.querySelector('[data-client-reset]');
    const empty = clientTable.querySelector('[data-client-empty]');
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
    let appliedFilters = {
        search: '',
        status: '',
        plan: '',
    };

    const getFilteredRows = () => {
        const query = appliedFilters.search.trim().toLowerCase();

        return rows.filter((row) => {
            const rowText = `${row.dataset.name} ${row.dataset.company} ${row.dataset.email}`.toLowerCase();
            const matchesSearch = !query || rowText.includes(query);
            const matchesStatus = !appliedFilters.status || row.dataset.status === appliedFilters.status;
            const matchesPlan = !appliedFilters.plan || row.dataset.plan === appliedFilters.plan;

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
        if (empty) {
            empty.hidden = filtered.length > 0;
        }

        summary.textContent = filtered.length
            ? `Showing ${start + 1}-${start + visible.length} of ${filtered.length} clients`
            : 'No clients found';
        current.textContent = `${page} / ${totalPages}`;

        first.disabled = page === 1;
        prev.disabled = page === 1;
        next.disabled = page === totalPages;
        last.disabled = page === totalPages;
    };

    const applyClientFilters = () => {
        appliedFilters = {
            search: search.value,
            status: status.value,
            plan: plan.value,
        };
        page = 1;
        render();
    };

    apply?.addEventListener('click', applyClientFilters);

    search?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyClientFilters();
        }
    });

    reset?.addEventListener('click', () => {
        search.value = '';
        status.value = '';
        plan.value = '';
        status.dispatchEvent(new Event('change', { bubbles: true }));
        plan.dispatchEvent(new Event('change', { bubbles: true }));
        appliedFilters = {
            search: '',
            status: '',
            plan: '',
        };
        page = 1;
        render();
    });

    perPage.addEventListener('input', () => {
        page = 1;
        render();
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
