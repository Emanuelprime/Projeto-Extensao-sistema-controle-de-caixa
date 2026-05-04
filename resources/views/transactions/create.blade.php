<x-admin-layout
    title="Novo Lançamento"
    subtitle="Registre entradas e saídas com categoria, competência e comprovante visual para auditoria interna."
>
    <x-slot:actions>
        <a href="{{ route('transactions.index') }}" class="secondary-button">Voltar ao extrato</a>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.55fr)_minmax(300px,0.85fr)]">
        <form method="POST" action="{{ route('transactions.store') }}"
              enctype="multipart/form-data"
              class="surface p-6 sm:p-8">
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
                    <input name="description" required class="field-control" type="text"
                           placeholder="Ex: Compra de materiais para oficina"
                           value="{{ old('description') }}">
                </label>

                <fieldset class="md:col-span-2">
                    <legend class="field-label">Tipo de movimentação</legend>
                    <div class="mt-2 grid gap-3 sm:grid-cols-2">
                        <label class="quiet-surface flex cursor-pointer items-center gap-3 p-4 transition hover:border-action">
                            <input type="radio" value="entrada" name="type"
                                   class="text-action focus:ring-action"
                                   {{ old('type', 'entrada') === 'entrada' ? 'checked' : '' }}>
                            <span>
                                <span class="block text-sm font-extrabold text-ink">Receita</span>
                                <span class="block text-xs font-semibold text-muted">Doações, repasses e entradas</span>
                            </span>
                        </label>
                        <label class="quiet-surface flex cursor-pointer items-center gap-3 p-4 transition hover:border-action">
                            <input type="radio" value="saida" name="type"
                                   class="text-action focus:ring-action"
                                   {{ old('type') === 'saida' ? 'checked' : '' }}>
                            <span>
                                <span class="block text-sm font-extrabold text-ink">Despesa</span>
                                <span class="block text-xs font-semibold text-muted">Gastos, reembolsos e pagamentos</span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                <label class="block">
                    <span class="field-label">Valor (R$)</span>
                    <input name="amount" required class="field-control" type="number"
                           step="0.01" placeholder="0.00" value="{{ old('amount') }}">
                </label>

                <label class="block">
                    <span class="field-label">Data de competência</span>
                    <input name="competencia_date" class="field-control" type="date"
                           value="{{ old('competencia_date') }}">
                    <span class="mt-1 text-xs text-muted">Se diferente da data de hoje</span>
                </label>

                <label class="block md:col-span-2">
                    <span class="field-label">Categoria do fluxo</span>
                    <select name="payment_method" class="field-control">
                        <option value="">Selecione uma categoria</option>
                        @foreach ($categories as $category)
                            <option {{ old('payment_method') === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="block md:col-span-2">
                    <span class="field-label">Observações complementares</span>
                    <textarea name="notes" class="field-control min-h-36"
                              placeholder="Detalhes adicionais sobre a transação, origem do recurso ou justificativa do gasto.">{{ old('notes') }}</textarea>
                </label>

                <div class="md:col-span-2">
                    <span class="field-label">Comprovante ou recibo</span>
                    <label id="receipt-label"
                           class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition hover:border-action hover:bg-blue-50">
                        <span class="text-sm font-extrabold text-action">Selecionar arquivo</span>
                        <span id="receipt-name" class="mt-2 text-xs font-semibold text-muted">PNG, JPG ou PDF até 10 MB</span>
                        <input id="receipt" name="receipt" type="file" class="sr-only"
                               accept=".png,.jpg,.jpeg,.pdf"
                               onchange="document.getElementById('receipt-name').textContent = this.files[0]?.name ?? 'PNG, JPG ou PDF até 10 MB'">
                    </label>
                    @error('receipt')
                        <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="reset" class="secondary-button">Cancelar</button>
                <button type="submit" class="primary-button">Confirmar registro</button>
            </div>
        </form>

        <aside class="space-y-6">
            <section class="surface p-6">
                <p class="eyebrow">Saldo do caixa aberto</p>
                @php
                    $activeRegister = \App\Models\CashRegister::where('user_id', auth()->id())
                        ->where('status', 'aberto')
                        ->with('transactions')
                        ->first();
                    if ($activeRegister) {
                        $entradas = $activeRegister->transactions->where('type', 'entrada')->sum('amount');
                        $saidas   = $activeRegister->transactions->where('type', 'saida')->sum('amount');
                        $saldoAtual = $activeRegister->opening_balance + $entradas - $saidas;
                    }
                @endphp
                @if($activeRegister)
                    <p class="mt-3 font-display text-3xl font-black text-navy-900">
                        R$ {{ number_format($saldoAtual, 2, ',', '.') }}
                    </p>
                    <p class="mt-2 text-sm font-semibold text-muted">Turno aberto em {{ $activeRegister->opened_at->format('d/m/Y \à\s H:i') }}.</p>
                @else
                    <p class="mt-3 text-sm font-semibold text-red-700">Nenhum caixa aberto no momento.</p>
                @endif
            </section>

            <section class="quiet-surface p-6">
                <p class="eyebrow">Lançamentos hoje</p>
                @php
                    $lancamentosHoje = \App\Models\Transaction::whereHas('cashRegister', fn($q) => $q->where('user_id', auth()->id()))
                        ->whereDate('created_at', today())
                        ->count();
                @endphp
                <p class="mt-3 font-display text-4xl font-black text-ink">{{ str_pad($lancamentosHoje, 2, '0', STR_PAD_LEFT) }}</p>
                <p class="mt-2 text-sm font-semibold text-muted">Lançamentos registrados hoje.</p>
            </section>

            <section class="quiet-surface p-6">
                <p class="eyebrow">Checklist interno</p>
                <ul class="mt-4 space-y-3 text-sm font-semibold text-slate-600">
                    <li>✓ Confirmar categoria antes do registro.</li>
                    <li>✓ Anexar recibo quando houver despesa.</li>
                    <li>✓ Usar observações para auditoria futura.</li>
                </ul>
            </section>
        </aside>
    </div>
</x-admin-layout>
