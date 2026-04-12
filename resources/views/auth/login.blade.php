<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Acesso interno | Instituto JP II</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="grid min-h-screen bg-white lg:grid-cols-[1.05fr_0.95fr]">
        <section class="relative hidden overflow-hidden bg-navy-900 lg:block">
            <img
                src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=1400&q=80"
                alt="Atividade socioeducativa com crianças em sala"
                class="h-full w-full object-cover opacity-70"
            >
            <div class="absolute inset-0 bg-gradient-to-tr from-navy-950 via-navy-900/80 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-12 text-white">
                <p class="eyebrow text-blue-100">Controle financeiro interno</p>
                <h1 class="mt-4 max-w-xl font-display text-5xl font-black leading-tight">Instituto de Ação Social João Paulo II</h1>
                <p class="mt-5 max-w-lg text-base font-medium leading-7 text-blue-50">
                    Registre entradas, despesas e comprovantes com clareza para auditoria e planejamento institucional.
                </p>
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-10">
                    <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-navy-900 font-display text-base font-black text-white">JP</span>
                    <p class="mt-8 eyebrow">Acesso restrito</p>
                    <h2 class="mt-3 font-display text-4xl font-black tracking-tight text-navy-900">Sistema Financeiro Interno</h2>
                    <p class="mt-3 text-sm font-medium leading-6 text-muted">
                        Entre com suas credenciais administrativas para acessar o painel de fluxo de caixa.
                    </p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    @if($errors->any())
                        <div class="rounded-lg bg-red-50 p-4 text-sm font-medium text-red-800">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <label class="block">
                        <span class="field-label">E-mail institucional</span>
                        <input class="field-control" type="email" name="email" value="admin@example.com" autocomplete="email">
                    </label>

                    <label class="block">
                        <span class="field-label">Senha</span>
                        <input class="field-control" type="password" name="password" value="password" autocomplete="current-password">
                    </label>

                    <div class="flex items-center justify-between gap-4">
                        <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                            <input type="checkbox" name="remember" class="rounded border-line text-action focus:ring-action" checked>
                            Manter acesso neste dispositivo
                        </label>
                        <a href="#" class="text-sm font-bold text-action hover:text-navy-900">Ajuda</a>
                    </div>

                    <button type="submit" class="primary-button w-full">Entrar no painel</button>
                </form>

                <p class="mt-8 rounded-lg bg-slate-50 p-4 text-xs font-semibold leading-5 text-muted">
                    Backend integrado! Use os dados preenchidos acima para testar.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
