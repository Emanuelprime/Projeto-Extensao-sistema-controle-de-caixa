import './bootstrap';

const sidebar = document.querySelector('[data-mobile-sidebar]');
const overlay = document.querySelector('[data-sidebar-overlay]');
const menuButton = document.querySelector('[data-mobile-menu-button]');
const closeButton = document.querySelector('[data-mobile-close-button]');
const collapsibleSidebar = document.querySelector('[data-collapsible-sidebar]');
const mainContent = document.querySelector('[data-main-content]');
const sidebarToggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
const sidebarToggleIcons = document.querySelectorAll('[data-sidebar-toggle-icon]');
const sidebarStorageKey = 'jp-finance-sidebar-collapsed';

function setSidebar(open) {
    if (!sidebar || !overlay || !menuButton) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full', !open);
    overlay.classList.toggle('hidden', !open);
    menuButton.setAttribute('aria-expanded', String(open));
}

menuButton?.addEventListener('click', () => setSidebar(true));
closeButton?.addEventListener('click', () => setSidebar(false));
overlay?.addEventListener('click', () => setSidebar(false));

function getStoredSidebarState() {
    try {
        return localStorage.getItem(sidebarStorageKey) === 'true';
    } catch {
        return false;
    }
}

function storeSidebarState(collapsed) {
    try {
        localStorage.setItem(sidebarStorageKey, String(collapsed));
    } catch {
        // localStorage can be unavailable in private or locked-down browsers.
    }
}

function applyDesktopSidebarState(collapsed) {
    if (!collapsibleSidebar || !mainContent) {
        return;
    }

    document.documentElement.classList.toggle('sidebar-collapsed-preload', collapsed);
    collapsibleSidebar.classList.toggle('is-collapsed', collapsed);
    collapsibleSidebar.classList.toggle('lg:w-20', collapsed);
    collapsibleSidebar.classList.toggle('lg:w-64', !collapsed);
    mainContent.classList.toggle('lg:pl-20', collapsed);
    mainContent.classList.toggle('lg:pl-64', !collapsed);

    sidebarToggleButtons.forEach((button) => {
        button.setAttribute('aria-expanded', String(!collapsed));
        button.setAttribute('aria-label', collapsed ? 'Expandir menu lateral' : 'Recolher menu lateral');
    });

    sidebarToggleIcons.forEach((icon) => {
        icon.innerHTML = collapsed ? '&rsaquo;' : '&lsaquo;';
    });
}

let isSidebarCollapsed = getStoredSidebarState();
applyDesktopSidebarState(isSidebarCollapsed);

sidebarToggleButtons.forEach((button) => {
    button.addEventListener('click', () => {
        isSidebarCollapsed = !isSidebarCollapsed;
        applyDesktopSidebarState(isSidebarCollapsed);
        storeSidebarState(isSidebarCollapsed);
    });
});

document.querySelectorAll('[data-demo-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const notice = form.querySelector('[data-demo-notice]');
        notice?.classList.remove('hidden');
    });
});

document.querySelectorAll('[data-file-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const target = document.querySelector(`[data-file-name="${input.id}"]`);

        if (!target) {
            return;
        }

        target.textContent = input.files?.[0]?.name || 'Nenhum arquivo selecionado';
    });
});

const categoryStorageKey = 'jp-finance-custom-categories';
const bankStorageKey = 'jp-finance-bank-names';
const accountStorageKey = 'jp-finance-bank-accounts';
const newCategoryValue = '__new_category__';
const newBankValue = '__new_bank__';

function readStoredList(key) {
    try {
        const parsed = JSON.parse(localStorage.getItem(key) || '[]');
        return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
    } catch {
        return [];
    }
}

function writeStoredList(key, values) {
    try {
        localStorage.setItem(key, JSON.stringify(values));
    } catch {
        // localStorage can be unavailable in private or locked-down browsers.
    }
}

function addStoredValue(key, value) {
    const cleanValue = value?.trim();

    if (!cleanValue) {
        return;
    }

    const values = readStoredList(key);
    const exists = values.some((item) => item.toLowerCase() === cleanValue.toLowerCase());

    if (!exists) {
        values.push(cleanValue);
        writeStoredList(key, values.sort((a, b) => a.localeCompare(b, 'pt-BR')));
    }
}

function selectHasValue(select, value) {
    return Array.from(select.options).some((option) => option.value.toLowerCase() === value.toLowerCase());
}

function appendStoredOptions(select, key, beforeValue = null) {
    const selectedValue = select.dataset.selectedValue || select.value;
    const beforeOption = beforeValue ? Array.from(select.options).find((option) => option.value === beforeValue) : null;

    readStoredList(key).forEach((value) => {
        if (selectHasValue(select, value)) {
            return;
        }

        const option = new Option(value, value);
        select.add(option, beforeOption || null);
    });

    if (selectedValue) {
        select.value = selectedValue;
    }
}

function reportFilterHasValue(container, value) {
    return Array.from(container.querySelectorAll('[data-report-option]')).some((option) => {
        return option.value.toLowerCase() === value.toLowerCase();
    });
}

function createReportFilterOption(value) {
    const label = document.createElement('label');
    label.className = 'flex cursor-pointer items-center gap-3 px-4 py-2.5 text-sm font-medium text-ink transition hover:bg-blue-50';

    const input = document.createElement('input');
    input.type = 'checkbox';
    input.value = value;
    input.dataset.reportOption = '';
    input.className = 'h-4 w-4 rounded border-line text-action focus:ring-action';

    const text = document.createElement('span');
    text.textContent = value;

    label.append(input, text);

    return label;
}

function appendStoredReportOptions(container) {
    const options = container.querySelector('[data-report-options]');
    const storageKey = container.dataset.reportStorageKey;

    if (!options || !storageKey) {
        return;
    }

    readStoredList(storageKey).forEach((value) => {
        if (!reportFilterHasValue(container, value)) {
            options.appendChild(createReportFilterOption(value));
        }
    });
}

function getSelectedReportOptions(container) {
    return Array.from(container.querySelectorAll('[data-report-option]'))
        .filter((option) => option.checked)
        .map((option) => option.value)
        .filter(Boolean);
}

function setReportFilterInputs(container, values) {
    const target = container.querySelector('[data-report-inputs]');
    const inputName = container.dataset.reportInputName;

    if (!target || !inputName) {
        return;
    }

    target.innerHTML = '';

    values.forEach((value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = inputName;
        input.value = value;
        target.appendChild(input);
    });
}

function updateReportMultiselect(container) {
    const selectedValues = getSelectedReportOptions(container);
    const allOption = container.querySelector('[data-report-all]');
    const label = container.querySelector('[data-report-label]');
    const emptyLabel = container.dataset.reportEmptyLabel || 'Todas as opções';
    const pluralLabel = container.dataset.reportPluralLabel || 'opções';

    if (allOption) {
        allOption.checked = selectedValues.length === 0;
    }

    if (label) {
        if (selectedValues.length === 0) {
            label.textContent = emptyLabel;
        } else if (selectedValues.length <= 2) {
            label.textContent = selectedValues.join(', ');
        } else {
            label.textContent = `${selectedValues[0]} + ${selectedValues.length - 1} ${pluralLabel}`;
        }
    }

    setReportFilterInputs(container, selectedValues);
}

function closeReportMultiselect(container) {
    const trigger = container.querySelector('[data-report-trigger]');
    const menu = container.querySelector('[data-report-menu]');

    menu?.classList.add('hidden');
    trigger?.setAttribute('aria-expanded', 'false');
}

document.querySelectorAll('[data-category-select]').forEach((select) => {
    const form = select.closest('form');
    const panel = form?.querySelector('[data-new-category-panel]');
    const input = form?.querySelector('[data-new-category-input]');
    const saveButton = form?.querySelector('[data-save-category]');
    const cancelButton = form?.querySelector('[data-cancel-category]');

    appendStoredOptions(select, categoryStorageKey, newCategoryValue);

    function closePanel() {
        panel?.classList.add('hidden');
        if (input) {
            input.value = '';
        }
    }

    function saveCategory() {
        const category = input?.value.trim();

        if (!category) {
            input?.focus();
            return false;
        }

        addStoredValue(categoryStorageKey, category);
        appendStoredOptions(select, categoryStorageKey, newCategoryValue);
        select.value = category;
        closePanel();
        document.querySelectorAll(`[data-report-multiselect][data-report-storage-key="${categoryStorageKey}"]`).forEach((container) => {
            appendStoredReportOptions(container);
            updateReportMultiselect(container);
        });
        return true;
    }

    select.addEventListener('change', () => {
        panel?.classList.toggle('hidden', select.value !== newCategoryValue);

        if (select.value === newCategoryValue) {
            input?.focus();
        }
    });

    saveButton?.addEventListener('click', saveCategory);
    cancelButton?.addEventListener('click', () => {
        select.value = '';
        closePanel();
    });

    form?.addEventListener('submit', (event) => {
        if (select.value === newCategoryValue && !saveCategory()) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-bank-select]').forEach((select) => {
    const form = select.closest('form');
    const panel = form?.querySelector('[data-new-bank-panel]');
    const input = form?.querySelector('[data-new-bank-input]');
    const saveButton = form?.querySelector('[data-save-bank]');
    const cancelButton = form?.querySelector('[data-cancel-bank]');

    appendStoredOptions(select, bankStorageKey, newBankValue);

    function closePanel() {
        panel?.classList.add('hidden');
        if (input) {
            input.value = '';
        }
    }

    function saveBank() {
        const bank = input?.value.trim();

        if (!bank) {
            input?.focus();
            return false;
        }

        addStoredValue(bankStorageKey, bank);
        appendStoredOptions(select, bankStorageKey, newBankValue);
        select.value = bank;
        closePanel();
        document.querySelectorAll(`[data-report-multiselect][data-report-storage-key="${bankStorageKey}"]`).forEach((container) => {
            appendStoredReportOptions(container);
            updateReportMultiselect(container);
        });
        return true;
    }

    select.addEventListener('change', () => {
        panel?.classList.toggle('hidden', select.value !== newBankValue);

        if (select.value === newBankValue) {
            input?.focus();
        }
    });

    saveButton?.addEventListener('click', saveBank);
    cancelButton?.addEventListener('click', () => {
        select.value = '';
        closePanel();
    });

    form?.addEventListener('submit', (event) => {
        if (select.value === newBankValue && !saveBank()) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-financial-entry-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const category = form.querySelector('[data-category-select]')?.value;
        const bank = form.querySelector('[data-bank-select]')?.value;
        const account = form.querySelector('[data-bank-account-input]')?.value;

        if (category && category !== newCategoryValue) {
            addStoredValue(categoryStorageKey, category);
        }

        if (bank && bank !== newBankValue) {
            addStoredValue(bankStorageKey, bank);
        }
        addStoredValue(accountStorageKey, account);
    });
});

document.querySelectorAll('[data-report-multiselect]').forEach((container) => {
    const trigger = container.querySelector('[data-report-trigger]');
    const menu = container.querySelector('[data-report-menu]');
    const allOption = container.querySelector('[data-report-all]');

    appendStoredReportOptions(container);
    updateReportMultiselect(container);

    trigger?.addEventListener('click', () => {
        const willOpen = menu?.classList.contains('hidden');
        menu?.classList.toggle('hidden');
        trigger.setAttribute('aria-expanded', String(willOpen));
    });

    allOption?.addEventListener('change', () => {
        container.querySelectorAll('[data-report-option]').forEach((option) => {
            option.checked = false;
        });
        updateReportMultiselect(container);
    });

    container.addEventListener('change', (event) => {
        if (!event.target.matches('[data-report-option]')) {
            return;
        }

        updateReportMultiselect(container);
    });
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('[data-report-multiselect]').forEach((container) => {
        if (!container.contains(event.target)) {
            closeReportMultiselect(container);
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    document.querySelectorAll('[data-report-multiselect]').forEach(closeReportMultiselect);
});
