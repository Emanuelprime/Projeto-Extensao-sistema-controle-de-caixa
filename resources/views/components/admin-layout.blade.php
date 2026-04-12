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

    <aside id="mobile-sidebar" data-mobile-sidebar class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col border-r border-line bg-white transition-transform duration-300 lg:w-64 lg:translate-x-0">
        <div class="flex h-20 items-center justify-between px-6">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-navy-900 font-display text-sm font-black text-white">JP</span>
                <span>
                    <span class="block text-sm font-extrabold uppercase tracking-[0.08em] text-navy-900">Sistema Financeiro</span>
                    <span class="block text-xs font-semibold text-muted">Instituto JP II</span>
                </span>
            </a>
            <button data-mobile-close-button type="button" class="ghost-button lg:hidden">Fechar</button>
        </div>

        <nav class="flex-1 space-y-1 px-4 py-4">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
            <x-nav-link :href="route('transactions.index')" :active="request()->routeIs('transactions.index')">Extrato</x-nav-link>
            <x-nav-link :href="route('transactions.create')" :active="request()->routeIs('transactions.create')">Lançamento</x-nav-link>
            <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.index')">Relatórios</x-nav-link>
        </nav>

        <div class="border-t border-line p-4">
            <a href="{{ route('transactions.create') }}" class="primary-button w-full">Novo lançamento</a>
            <div class="mt-6 rounded-lg bg-slate-50 p-4">
                <p class="eyebrow">Sessão interna</p>
                <p class="mt-2 text-sm font-bold text-ink">Admin Financeiro</p>
                <p class="text-xs font-medium text-muted">Dados demonstrativos</p>
            </div>
            <a href="{{ route('login') }}" class="mt-3 flex rounded-lg px-3 py-2 text-sm font-bold text-slate-500 transition hover:bg-slate-50 hover:text-danger">Sair</a>
        </div>
    </aside>

    <main class="lg:pl-64">
        <div class="hidden h-16 items-center justify-between border-b border-line bg-white px-8 lg:flex">
            <div class="relative w-full max-w-sm">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400">Buscar</span>
                <input type="search" class="field-control mt-0 pl-16" placeholder="transações, categorias, recibos...">
            </div>
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
