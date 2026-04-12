<x-admin-layout
    title="Relatórios"
    subtitle="Centralize exportações para auditoria, prestação de contas e conferência mensal."
>
    <x-slot:actions>
        <button type="button" class="secondary-button">Atualizar dados</button>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.85fr)]">
        <section class="surface p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="eyebrow">Distribuição mensal</p>
                    <h2 class="mt-2 font-display text-3xl font-black text-navy-900">R$ 142.450,00</h2>
                    <p class="mt-2 text-sm font-bold text-emerald-700">+12% vs mês anterior</p>
                </div>
                <div class="flex rounded-lg border border-line bg-slate-50 p-1">
                    <button class="rounded-lg bg-white px-4 py-2 text-xs font-extrabold uppercase text-action shadow-sm">Mensal</button>
                    <button class="px-4 py-2 text-xs font-extrabold uppercase text-slate-500">Trimestral</button>
                    <button class="px-4 py-2 text-xs font-extrabold uppercase text-slate-500">Anual</button>
                </div>
            </div>

            <div class="mt-8 space-y-5">
                @foreach ($distribution as $item)
                    <div>
                        <div class="flex items-center justify-between text-sm font-bold">
                            <span>{{ $item['label'] }}</span>
                            <span>{{ $item['value'] }}%</span>
                        </div>
                        <div class="mt-2 h-3 rounded-full bg-slate-100">
                            <div class="h-3 rounded-full bg-navy-900" style="width: {{ $item['value'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <aside class="surface p-6 sm:p-8">
            <p class="eyebrow">Exportação financeira</p>
            <h2 class="mt-3 text-2xl font-extrabold leading-tight text-ink">Arquivos consolidados para auditoria</h2>
            <p class="mt-3 text-sm font-medium leading-6 text-muted">
                Gere relatórios em PDF ou CSV quando o back-end de exportação estiver conectado.
            </p>

            <div class="mt-8 space-y-3">
                <button type="button" class="primary-button w-full">Relatório completo (PDF)</button>
                <button type="button" class="secondary-button w-full">Dados brutos (CSV)</button>
            </div>
        </aside>
    </div>

    <section class="mt-6 surface overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-line px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="eyebrow">Histórico de exportações recentes</p>
                <h2 class="mt-1 text-xl font-extrabold text-ink">Arquivos gerados</h2>
            </div>
            <div class="flex gap-4 text-xs font-extrabold uppercase text-muted">
                <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span>Processado</span>
                <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-danger"></span>Falha</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px]">
                <thead class="table-head">
                    <tr>
                        <th class="px-6 py-3">Documento</th>
                        <th class="px-6 py-3">Data de geração</th>
                        <th class="px-6 py-3">Formato</th>
                        <th class="px-6 py-3">Tamanho</th>
                        <th class="px-6 py-3">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($exports as $export)
                        <tr class="transition hover:bg-slate-50">
                            <td class="table-cell">
                                <span class="block font-bold text-ink">{{ $export['document'] }}</span>
                                <span class="text-xs font-semibold text-muted">{{ $export['description'] }}</span>
                            </td>
                            <td class="table-cell font-semibold text-slate-600">{{ $export['date'] }}</td>
                            <td class="table-cell"><x-status-badge :status="$export['format']" /></td>
                            <td class="table-cell font-semibold text-slate-600">{{ $export['size'] }}</td>
                            <td class="table-cell">
                                @if ($export['status'] === 'Falha')
                                    <button class="ghost-button text-danger">Revisar</button>
                                @else
                                    <button class="ghost-button">Baixar</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-6 quiet-surface p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="grid gap-4 sm:grid-cols-2">
                <p class="text-sm font-semibold text-muted"><span class="font-extrabold text-ink">Total em cache:</span> R$ 1.2M</p>
                <p class="text-sm font-semibold text-muted"><span class="font-extrabold text-ink">Exportados hoje:</span> 04 arq.</p>
            </div>
            <p class="text-sm font-semibold text-muted">Última atualização: hoje às 14:30</p>
        </div>
    </section>
</x-admin-layout>
