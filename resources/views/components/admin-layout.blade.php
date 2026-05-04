@props([
    'title' => 'Sistema Financeiro',
    'subtitle' => null,
])

<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title }} | Instituto JP II</title>
    <script>
        (function () {
            try {
                if (localStorage.getItem('jp-finance-sidebar-collapsed') === 'true') {
                    document.documentElement.classList.add('sidebar-collapsed-preload');
                }
            } catch (error) {
                document.documentElement.classList.remove('sidebar-collapsed-preload');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div data-sidebar-overlay class="fixed inset-0 z-30 hidden bg-slate-950/40 lg:hidden"></div>

    <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-line bg-white/95 px-4 backdrop-blur lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-navy-900 font-display text-sm font-black text-white">JP</span>
            <span>
                <span class="block text-sm font-extrabold uppercase tracking-[0.08em] text-navy-900">Instituto JP II</span>
                <span class="block text-xs font-semibold text-muted">Controle interno</span>
            </span>
        </a>
        <button data-mobile-menu-button type="button" aria-expanded="false" aria-controls="mobile-sidebar" class="secondary-button px-3 py-2">
            Menu
        </button>
    </header>

    <aside id="mobile-sidebar" data-mobile-sidebar data-collapsible-sidebar class="sidebar-shell fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-line bg-white transition-transform duration-300 lg:w-64 lg:translate-x-0 lg:transition-[width] lg:ease-out">
        <div class="sidebar-header flex h-20 items-center justify-between px-6">
            <a href="{{ route('dashboard') }}" class="sidebar-brand flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-navy-900 font-display text-sm font-black text-white">JP</span>
                <span class="sidebar-brand-text min-w-0">
                    <span class="block text-sm font-extrabold uppercase tracking-[0.08em] text-navy-900">Sistema Financeiro</span>
                    <span class="block text-xs font-semibold text-muted">Instituto JP II</span>
                </span>
            </a>
            <button data-sidebar-toggle type="button" aria-expanded="true" aria-label="Recolher menu lateral" class="sidebar-toggle-button hidden h-9 w-9 items-center justify-center rounded-lg border border-line bg-white text-xl font-black leading-none text-navy-900 shadow-sm transition hover:border-action hover:text-action focus:outline-none focus:ring-2 focus:ring-action focus:ring-offset-2 lg:absolute lg:-right-4 lg:top-5 lg:z-50 lg:inline-flex">
                <span data-sidebar-toggle-icon aria-hidden="true">&lsaquo;</span>
            </button>
            <button data-mobile-close-button type="button" class="ghost-button lg:hidden">Fechar</button>
        </div>

        <nav class="sidebar-nav flex-1 space-y-1 px-4 py-4">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard">Dashboard</x-nav-link>
            <x-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.index')" icon="statement">Extrato</x-nav-link>
            <x-nav-link :href="route('transactions.create')" :active="request()->routeIs('transactions.create')" icon="entry">Lançamento</x-nav-link>
            <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.index')" icon="reports">Relatórios</x-nav-link>
            @if(Auth::user()->role === 'admin')
                <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon="admins">Usuários</x-nav-link>
            @endif
        </nav>

        <div class="sidebar-footer border-t border-line p-4">
            <div class="sidebar-footer-panel rounded-lg bg-slate-50 p-4">
                <p class="eyebrow">Sessão</p>
                <p class="mt-2 text-sm font-bold text-ink">{{ Auth::user()->name }}</p>
                <p class="text-xs font-medium text-muted capitalize">{{ Auth::user()->role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="sidebar-logout flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-bold text-slate-500 transition hover:bg-slate-50 hover:text-danger">
                    <svg class="sidebar-icon h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <path d="M16 17l5-5-5-5" />
                        <path d="M21 12H9" />
                    </svg>
                    <span class="sidebar-label truncate">Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <main data-main-content class="lg:pl-64 lg:transition-[padding] lg:duration-300 lg:ease-out">
        <div class="hidden h-16 items-center justify-between border-b border-line bg-white px-8 lg:flex">
            <form method="GET" action="{{ url()->current() }}" class="flex w-full max-w-xl items-center gap-2">
                <input name="q" type="search" value="{{ request('q') }}" class="field-control mt-0" placeholder="Buscar transações, categorias, recibos...">
                <button type="submit" class="primary-button shrink-0 px-5 py-3">Buscar</button>
            </form>
            <div class="flex items-center gap-3">
                <span class="text-right">
                    <span class="block text-sm font-bold text-ink">Admin User</span>
                    <span class="block text-xs font-semibold uppercase tracking-[0.08em] text-muted">Controladoria</span>
                </span>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-navy-900 text-sm font-black text-white">AU</span>
            </div>
        </div>

        <section class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="eyebrow">Gestão de fluxo</p>
                    <h1 class="mt-2 font-display text-3xl font-black tracking-tight text-navy-900">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-muted">{{ $subtitle }}</p>
                    @endif
                </div>
                @isset($actions)
                    <div class="flex flex-wrap gap-3">{{ $actions }}</div>
                @endisset
            </div>

            <div class="motion-enter">
                {{ $slot }}
            </div>
        </section>
    </main>
</body>
</html>
