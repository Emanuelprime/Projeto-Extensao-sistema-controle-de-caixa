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
