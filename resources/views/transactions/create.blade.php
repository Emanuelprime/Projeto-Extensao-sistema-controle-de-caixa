<x-admin-layout
    title="Novo Lançamento"
    subtitle="Registre entradas e saídas com categoria, competência e comprovante visual para auditoria interna."
>
    <x-slot:actions>
        <a href="{{ route('transactions.index') }}" class="secondary-button">Voltar ao extrato</a>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(300px,0.85fr)]">
        <form method="POST" action="{{ route('transactions.store') }}" class="surface p-6 sm:p-8">
            @csrf
            
            @if($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm font-medium text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            <div class="grid gap-5 md:grid-cols-2">
                <label class="block md:col-span-2">
                    <span class="field-label">Título do lançamento</span>
                    <input name="description" required class="field-control" type="text" placeholder="Ex: Compra de materiais para oficina">
                </label>

                <fieldset class="md:col-span-2">
                    <legend class="field-label">Tipo de movimentação</legend>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                        <label class="quiet-surface flex cursor-pointer items-center gap-3 p-4 transition hover:border-action">
                            <input type="radio" value="entrada" name="type" class="text-action focus:ring-action" checked>
                            <span>
                                <span class="block text-sm font-extrabold text-ink">Receita</span>
                                <span class="block text-xs font-semibold text-muted">Doações, repasses e entradas</span>
                            </span>
                        </label>
                        <label class="quiet-surface flex cursor-pointer items-center gap-3 p-4 transition hover:border-action">
                            <input type="radio" value="saida" name="type" class="text-action focus:ring-action">
                            <span>
                                <span class="block text-sm font-extrabold text-ink">Despesa</span>
                                <span class="block text-xs font-semibold text-muted">Gastos, reembolsos e pagamentos</span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <label class="block">
                    <span class="field-label">Valor (R$)</span>
                    <input name="amount" required class="field-control" type="number" step="0.01" placeholder="0.00">
                </label>

                <label class="block">
                    <span class="field-label">Data de competência</span>
                    <input class="field-control" type="date">
                </label>

                <label class="block md:col-span-2">
                    <span class="field-label">Categoria do fluxo</span>
                    <select name="payment_method" class="field-control">
                        <option>Selecione uma categoria</option>
                        @foreach ($categories as $category)
                            <option>{{ $category }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block md:col-span-2">
                    <span class="field-label">Observações complementares</span>
                    <textarea class="field-control min-h-36" placeholder="Detalhes adicionais sobre a transação, origem do recurso ou justificativa do gasto."></textarea>
                </label>

                <div class="md:col-span-2">
                    <span class="field-label">Comprovante ou recibo</span>
                    <label class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition hover:border-action hover:bg-blue-50">
                        <span class="text-sm font-extrabold text-action">Selecionar arquivo</span>
                        <span data-file-name="receipt" class="mt-2 text-xs font-semibold text-muted">PNG, JPG ou PDF até 10 MB</span>
                        <input id="receipt" data-file-input type="file" class="sr-only" accept=".png,.jpg,.jpeg,.pdf">
                    </label>
                </div>
            </div>
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="reset" class="secondary-button">Cancelar</button>
                <button type="submit" class="primary-button">Confirmar registro</button>
            </div>
        </form>

        <aside class="space-y-6">
            <section class="quiet-surface p-6">
                <p class="eyebrow">Checklist interno</p>
                <ul class="mt-4 space-y-3 text-sm font-semibold text-slate-600">
                    <li>Confirmar categoria antes do registro.</li>
                    <li>Anexar recibo ou comprovante.</li>
                    <li>Usar observações para auditoria futura.</li>
                </ul>
            </section>
        </aside>
    </div>
</x-admin-layout>
