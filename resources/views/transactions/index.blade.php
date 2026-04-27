<x-admin-layout
    title="Extrato Detalhado"
    subtitle="Filtre movimentações por período, categoria e status para conferir a aplicação dos recursos."
>
    <x-slot:actions>
        <a href="{{ route('transactions.create') }}" class="primary-button">Novo lançamento</a>
        <button type="button" class="secondary-button">Exportar CSV</button>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach ($summary as $item)
            <x-kpi-card :label="$item['label']" :value="$item['value']" :detail="$item['detail']" :tone="$item['tone']" />
        @endforeach
    </div>

    @php($transactionCount = count($transactions))

    <section class="mt-6 quiet-surface p-5">
        <div class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
            <label class="block">
                <span class="field-label">Período inicial</span>
                <input class="field-control" type="date">
            </label>
            <label class="block">
                <span class="field-label">Período final</span>
                <input class="field-control" type="date">
            </label>
            <label class="block">
                <span class="field-label">Categoria</span>
                <select class="field-control">
                    <option>Todas as categorias</option>
                    <option>Doações</option>
                    <option>Oficinas</option>
                    <option>Manutenção</option>
                </select>
            </label>
            <label class="block">
                <span class="field-label">Status</span>
                <select class="field-control">
                    <option>Todos</option>
                    <option>Liquidados</option>
                    <option>Pendentes</option>
                </select>
            </label>
            <button type="button" class="primary-button">Aplicar filtros</button>
        </div>
    </section>

    <section class="mt-6 surface overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-line px-6 py-5 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="eyebrow">Transações</p>
                <h2 class="mt-1 text-xl font-extrabold text-ink">
                    @if ($transactionCount === 0)
                        Nenhuma movimentação encontrada
                    @else
                        Mostrando {{ $transactionCount }} {{ $transactionCount === 1 ? 'movimentação' : 'movimentações' }}
                    @endif
                </h2>
            </div>
            <input type="search" class="field-control mt-0 max-w-sm" placeholder="Buscar transação...">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px]">
                <thead class="table-head">
                    <tr>
                        <th class="px-6 py-3">Data</th>
                        <th class="px-6 py-3">Descrição</th>
                        <th class="px-6 py-3">Categoria</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr class="transition hover:bg-slate-50">
                            <td class="table-cell font-bold text-slate-500">{{ $transaction['date'] }}</td>
                            <td class="table-cell font-bold text-ink">{{ $transaction['description'] }}</td>
                            <td class="table-cell font-semibold text-slate-600">{{ $transaction['category'] }}</td>
                            <td class="table-cell"><x-status-badge :status="$transaction['status']" /></td>
                            <td @class([
                                'table-cell text-right font-extrabold',
                                'text-emerald-700' => str($transaction['value'])->startsWith('+'),
                                'text-danger' => str($transaction['value'])->startsWith('-'),
                            ])>{{ $transaction['value'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admin-layout>
