<x-admin-layout
    title="Relatórios"
    subtitle="Centralize exportações para auditoria, prestação de contas e conferência mensal."
>
    <x-slot:actions>
        <a href="{{ route('reports.index') }}" class="secondary-button">Atualizar dados</a>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.85fr)]">

        {{-- Distribuição por categoria --}}
        <section class="surface p-6 sm:p-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="eyebrow">Distribuição por categoria</p>
                    <h2 class="mt-2 font-display text-3xl font-black text-navy-900">
                        R$ {{ number_format($totalEntradas + $totalSaidas, 2, ',', '.') }}
                    </h2>
                    <p class="mt-2 text-sm font-bold text-slate-500">
                        Entradas: <span class="text-emerald-700">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</span>
                        &nbsp;·&nbsp;
                        Saídas: <span class="text-danger">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</span>
                    </p>
                </div>
            </div>

            <div class="mt-8 space-y-5">
                @forelse ($distributionFormatted as $item)
                    <div>
                        <div class="flex items-center justify-between text-sm font-bold">
                            <span>{{ $item['label'] }}</span>
                            <span>{{ $item['value'] }}% &middot; R$ {{ number_format($item['amount'], 2, ',', '.') }}</span>
                        </div>
                        <div class="mt-2 h-3 rounded-full bg-slate-100">
                            <div class="h-3 rounded-full bg-navy-900 transition-all" style="width: {{ $item['value'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm font-semibold text-muted">Nenhuma transação registrada ainda.</p>
                @endforelse
            </div>
        </section>

        {{-- Painel de exportação --}}
        <aside class="surface p-6 sm:p-8">
            <p class="eyebrow">Exportação financeira</p>
            <h2 class="mt-3 text-2xl font-extrabold leading-tight text-ink">Arquivos consolidados para auditoria</h2>
            <p class="mt-3 text-sm font-medium leading-6 text-muted">
                Gere relatórios em PDF ou CSV com todos os lançamentos do seu histórico.
            </p>

            <div class="mt-8 space-y-3">
                <a href="{{ route('transactions.export_pdf') }}"
                   class="primary-button w-full text-center block">
                    ↓ Relatório completo (PDF)
                </a>
                <a href="{{ route('transactions.export_csv') }}"
                   class="secondary-button w-full text-center block">
                    ↓ Dados brutos (CSV)
                </a>
            </div>

            <div class="mt-6 rounded-lg bg-slate-50 p-4 text-sm text-muted">
                <p class="font-extrabold text-ink">Dica</p>
                <p class="mt-1">Para exportar um período específico, aplique os filtros no
                    <a href="{{ route('transactions.index') }}" class="font-bold text-action underline">Extrato</a>
                    e use os botões de exportação de lá.
                </p>
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
                    @forelse ($exports as $export)
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
                    @empty
                        <tr>
                            <td colspan="5" class="table-cell py-10 text-center text-sm font-semibold text-muted">
                                Nenhuma exportação recente encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Saldo consolidado --}}
    <section class="mt-6 quiet-surface p-5">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="grid gap-4 sm:grid-cols-3">
                <p class="text-sm font-semibold text-muted">
                    <span class="font-extrabold text-ink">Total de entradas:</span>
                    R$ {{ number_format($totalEntradas, 2, ',', '.') }}
                </p>
                <p class="text-sm font-semibold text-muted">
                    <span class="font-extrabold text-ink">Total de saídas:</span>
                    R$ {{ number_format($totalSaidas, 2, ',', '.') }}
                </p>
                <p class="text-sm font-semibold text-muted">
                    <span class="font-extrabold text-ink">Saldo líquido:</span>
                    <span @class(['text-emerald-700 font-extrabold' => $saldo >= 0, 'text-danger font-extrabold' => $saldo < 0])>
                        R$ {{ number_format($saldo, 2, ',', '.') }}
                    </span>
                </p>
            </div>
            <p class="text-sm font-semibold text-muted">Atualizado em {{ now()->format('d/m/Y \à\s H:i') }}</p>
        </div>
    </section>
</x-admin-layout>
