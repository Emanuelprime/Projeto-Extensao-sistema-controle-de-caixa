@php use Illuminate\Support\Facades\Storage; @endphp
<x-admin-layout
    title="Extrato Detalhado"
    subtitle="Filtre movimentações por período, categoria e status para conferir a aplicação dos recursos."
>
    <x-slot:actions>
        <a href="{{ route('transactions.create') }}" class="primary-button">Novo lançamento</a>
        <a href="{{ route('transactions.export_csv', request()->query()) }}" class="secondary-button">Exportar CSV</a>
        <a href="{{ route('transactions.export_pdf', request()->query()) }}" class="secondary-button">Exportar PDF</a>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach ($summary as $item)
            <x-kpi-card :label="$item['label']" :value="$item['value']" :detail="$item['detail']" :tone="$item['tone']" />
        @endforeach
    </div>

    {{-- Filtros --}}
    <section class="mt-6 quiet-surface p-5">
        <form method="GET" action="{{ route('transactions.index') }}">
            <div class="grid gap-4 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
                <label class="block">
                    <span class="field-label">Data inicial</span>
                    <input class="field-control" type="date" name="date_start" value="{{ request('date_start') }}">
                </label>
                <label class="block">
                    <span class="field-label">Data final</span>
                    <input class="field-control" type="date" name="date_end" value="{{ request('date_end') }}">
                </label>
                <label class="block">
                    <span class="field-label">Tipo</span>
                    <select class="field-control" name="type">
                        <option value="">Todos os tipos</option>
                        <option value="entrada" @selected(request('type') === 'entrada')>Receita</option>
                        <option value="saida"   @selected(request('type') === 'saida')>Despesa</option>
                    </select>
                </label>
                <label class="block">
                    <span class="field-label">Categoria</span>
                    <select class="field-control" name="payment_method">
                        <option value="">Todas as categorias</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}" @selected(request('payment_method') === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="primary-button">Aplicar filtros</button>
            </div>
        </form>
    </section>

    <section class="mt-6 surface overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-line px-6 py-5 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="eyebrow">Transações</p>
                <h2 class="mt-1 text-xl font-extrabold text-ink">
                    Mostrando {{ $paginated->count() }} de {{ $paginated->total() }} movimentações
                </h2>
            </div>
            <form method="GET" action="{{ route('transactions.index') }}" class="flex gap-2">
                @foreach(request()->except('search', 'page') as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <input type="search" name="search" class="field-control mt-0 max-w-sm"
                       placeholder="Buscar transação..." value="{{ request('search') }}">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead class="table-head">
                    <tr>
                        <th class="px-6 py-3">Data</th>
                        <th class="px-6 py-3">Descrição</th>
                        <th class="px-6 py-3">Categoria</th>
                        <th class="px-6 py-3">Tipo</th>
                        <th class="px-6 py-3 text-right">Valor</th>
                        <th class="px-6 py-3">Comprovante</th>
                        <th class="px-6 py-3">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($paginated as $t)
                        <tr class="transition hover:bg-slate-50">
                            <td class="table-cell font-bold text-slate-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                            <td class="table-cell font-bold text-ink">{{ $t->description }}</td>
                            <td class="table-cell font-semibold text-slate-600">{{ $t->payment_method ?? '-' }}</td>
                            <td class="table-cell">
                                @if($t->type === 'entrada')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-extrabold text-emerald-700">↑ Receita</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs font-extrabold text-red-700">↓ Despesa</span>
                                @endif
                            </td>
                            <td @class([
                                'table-cell text-right font-extrabold',
                                'text-emerald-700' => $t->type === 'entrada',
                                'text-danger'      => $t->type === 'saida',
                            ])>
                                {{ ($t->type === 'entrada' ? '+' : '-') . ' R$ ' . number_format($t->amount, 2, ',', '.') }}
                            </td>
                            <td class="table-cell">
                                @if($t->receipt_path)
                                    <a href="{{ Storage::url($t->receipt_path) }}"
                                       target="_blank"
                                       class="ghost-button text-action text-xs">
                                        Ver comprovante
                                    </a>
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>
                            <td class="table-cell">
                                <form method="POST" action="{{ route('transactions.destroy', $t->id) }}"
                                      onsubmit="return confirm('Excluir este lançamento?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ghost-button text-danger text-xs">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-cell py-10 text-center text-sm font-semibold text-muted">
                                Nenhum lançamento encontrado para os filtros selecionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($paginated->hasPages())
            <div class="px-6 py-5">
                {{ $paginated->links() }}
            </div>
        @endif
    </section>
</x-admin-layout>
