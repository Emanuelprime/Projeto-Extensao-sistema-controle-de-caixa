import './bootstrap';

const sidebar = document.querySelector('[data-mobile-sidebar]');
const overlay = document.querySelector('[data-sidebar-overlay]');
const menuButton = document.querySelector('[data-mobile-menu-button]');
const closeButton = document.querySelector('[data-mobile-close-button]');

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
