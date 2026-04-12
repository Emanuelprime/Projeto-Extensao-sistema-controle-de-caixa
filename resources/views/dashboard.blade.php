<x-admin-layout
    title="Painel Principal"
    subtitle="Acompanhe saldo, movimentações recentes e distribuição de gastos do fluxo de caixa institucional."
>
    <x-slot:actions>
        <a href="{{ route('transactions.create') }}" class="primary-button">Novo lançamento</a>
        <a href="{{ route('transactions.index') }}" class="secondary-button">Ver extrato</a>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach ($indicators as $indicator)
            <x-kpi-card :label="$indicator['label']" :value="$indicator['value']" :detail="$indicator['detail']" :tone="$indicator['tone']" />
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(300px,1fr)]">
        <section class="surface overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="eyebrow">Lançamentos recentes</p>
                    <h2 class="mt-1 text-xl font-extrabold text-ink">Movimentação do caixa</h2>
                </div>
                <a href="{{ route('transactions.index') }}" class="ghost-button">Ver extrato completo</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px]">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-3">Data</th>
                            <th class="px-6 py-3">Descrição</th>
                            <th class="px-6 py-3">Categoria</th>
                            <th class="px-6 py-3 text-right">Valor</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaction)
                            <tr class="transition hover:bg-slate-50">
                                <td class="table-cell font-bold text-slate-500">{{ $transaction['date'] }}</td>
                                <td class="table-cell">
                                    <span class="block font-bold text-ink">{{ $transaction['description'] }}</span>
                                    <span class="text-xs font-semibold uppercase tracking-[0.08em] text-muted">{{ $transaction['type'] }}</span>
                                </td>
                                <td class="table-cell font-semibold text-slate-600">{{ $transaction['category'] }}</td>
                                <td @class([
                                    'table-cell text-right font-extrabold',
                                    'text-emerald-700' => str($transaction['value'])->startsWith('+'),
                                    'text-danger' => str($transaction['value'])->startsWith('-'),
                                ])>{{ $transaction['value'] }}</td>
                                <td class="table-cell"><x-status-badge :status="$transaction['status']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="surface p-6">
                <p class="eyebrow">Distribuição de gastos</p>
                <h2 class="mt-1 text-xl font-extrabold text-ink">Competência atual</h2>
                <div class="mt-6 space-y-5">
                    @foreach ($distribution as $item)
                        <div>
                            <div class="flex items-center justify-between text-sm font-bold">
                                <span>{{ $item['label'] }}</span>
                                <span>{{ $item['value'] }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-action" style="width: {{ $item['value'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="quiet-surface p-6">
                <p class="eyebrow">Meta mensal</p>
                <div class="mt-4 flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-lg border border-line text-lg font-black text-action">82%</div>
                    <div>
                        <p class="text-sm font-bold uppercase text-slate-500">Prestação registrada</p>
                        <p class="mt-1 font-display text-2xl font-black text-ink">R$ 55.000,00</p>
                    </div>
                </div>
                <p class="mt-5 text-sm font-medium leading-6 text-muted">Faltam R$ 9.800,00 para fechar a meta prevista para o mês.</p>
            </section>

            <section class="quiet-surface p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="eyebrow">Status conexão</p>
                        <p class="mt-2 text-sm font-bold text-ink">Conciliação manual ativa</p>
                    </div>
                    <x-status-badge status="Liquidado" />
                </div>
            </section>
        </aside>
    </div>
</x-admin-layout>
