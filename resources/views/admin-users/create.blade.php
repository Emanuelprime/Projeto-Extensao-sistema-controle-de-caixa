<x-admin-layout
    title="Novo Administrador"
    subtitle="Cadastre o acesso de administradores internos com os dados previstos para a conta do usuário."
>
    <x-slot:actions>
        <a href="{{ route('dashboard') }}" class="secondary-button">Voltar ao painel</a>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(300px,0.85fr)]">
        <form data-demo-form method="POST" action="#" class="surface p-6 sm:p-8">
            <div class="mb-6 border-b border-line pb-6">
                <div>
                    <p class="eyebrow">Acesso administrativo</p>
                    <h2 class="mt-1 text-xl font-extrabold text-ink">Dados do novo administrador</h2>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <label class="block md:col-span-2">
                    <span class="field-label">Nome completo</span>
                    <input name="name" class="field-control" type="text" placeholder="Ex: Maria Oliveira" autocomplete="name">
                </label>

                <label class="block">
                    <span class="field-label">E-mail institucional</span>
                    <input name="email" class="field-control" type="email" placeholder="admin@institutojp2.org" autocomplete="email">
                </label>

                <label class="block">
                    <span class="field-label">Senha temporária</span>
                    <input name="password" class="field-control" type="password" placeholder="Defina uma senha inicial" autocomplete="new-password">
                </label>

                <label class="block">
                    <span class="field-label">Confirmar senha</span>
                    <input name="password_confirmation" class="field-control" type="password" placeholder="Repita a senha inicial" autocomplete="new-password">
                </label>

                <label class="block">
                    <span class="field-label">Perfil de acesso</span>
                    <select name="role" class="field-control">
                        <option value="admin">Administrador</option>
                        <option value="controladoria">Controladoria</option>
                        <option value="operacional">Operacional</option>
                    </select>
                </label>

                <label class="block">
                    <span class="field-label">Status inicial</span>
                    <select name="status" class="field-control">
                        <option>Ativo</option>
                        <option>Bloqueado</option>
                    </select>
                </label>
            </div>
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="reset" class="secondary-button">Limpar campos</button>
                <button type="submit" class="primary-button">Criar admin</button>
            </div>
        </form>

        <aside class="space-y-6">
            <section class="surface p-6">
                <p class="eyebrow">Regra de criação</p>
                <h2 class="mt-2 text-xl font-extrabold text-ink">Apenas administradores</h2>
                <p class="mt-3 text-sm font-medium leading-6 text-muted">
                    Esta tela representa o fluxo visual para usuários administradores criarem outros administradores.
                </p>
            </section>

            <section class="quiet-surface p-6">
                <p class="eyebrow">Sessão atual</p>
                <div class="mt-4 flex items-center gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-navy-900 text-sm font-black text-white">AU</span>
                    <div>
                        <p class="text-sm font-extrabold text-ink">Admin User</p>
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-muted">Controladoria</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</x-admin-layout>