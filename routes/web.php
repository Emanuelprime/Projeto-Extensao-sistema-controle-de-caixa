<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/dashboard', function () {
    $indicators = [
        ['label' => 'Saldo atualizado', 'value' => 'R$ 142.850,00', 'detail' => 'Caixa consolidado para uso interno', 'tone' => 'blue'],
        ['label' => 'Entradas do mês', 'value' => 'R$ 45.200,00', 'detail' => '+12,5% em relação ao mês anterior', 'tone' => 'green'],
        ['label' => 'Saídas do mês', 'value' => 'R$ 12.340,50', 'detail' => 'Dentro do orçamento planejado', 'tone' => 'red'],
    ];

    $transactions = [
        ['date' => '14 Out 2026', 'description' => 'Doação recorrente - comunidade local', 'category' => 'Doações', 'type' => 'Receita', 'value' => '+ R$ 15.000,00', 'status' => 'Liquidado'],
        ['date' => '13 Out 2026', 'description' => 'Compra de materiais para oficinas', 'category' => 'Oficinas', 'type' => 'Despesa', 'value' => '- R$ 4.200,00', 'status' => 'Liquidado'],
        ['date' => '12 Out 2026', 'description' => 'Manutenção predial emergencial', 'category' => 'Manutenção', 'type' => 'Despesa', 'value' => '- R$ 2.850,90', 'status' => 'Agendado'],
        ['date' => '10 Out 2026', 'description' => 'Repasses para alimentação', 'category' => 'Alimentação', 'type' => 'Despesa', 'value' => '- R$ 499,00', 'status' => 'Liquidado'],
        ['date' => '08 Out 2026', 'description' => 'Reembolso de transporte voluntário', 'category' => 'Transporte', 'type' => 'Despesa', 'value' => '- R$ 1.230,00', 'status' => 'Pendente'],
    ];

    $distribution = [
        ['label' => 'Oficinas', 'value' => 45],
        ['label' => 'Pessoal', 'value' => 28],
        ['label' => 'Manutenção', 'value' => 15],
        ['label' => 'Outros', 'value' => 12],
    ];

    return view('dashboard', compact('indicators', 'transactions', 'distribution'));
})->name('dashboard');

Route::get('/lancamentos/novo', function () {
    $categories = ['Doações', 'Repasses', 'Oficinas', 'Manutenção', 'Pessoal', 'Alimentação', 'Transporte', 'Materiais'];

    return view('transactions.create', compact('categories'));
})->name('transactions.create');

Route::get('/extrato', function () {
    $summary = [
        ['label' => 'Saldo total consolidado', 'value' => 'R$ 142.850,00', 'detail' => '+12,4% em relação ao mês anterior', 'tone' => 'blue'],
        ['label' => 'Entradas pendentes', 'value' => 'R$ 15.200,00', 'detail' => 'Próximos 7 dias', 'tone' => 'green'],
        ['label' => 'Saídas a vencer', 'value' => 'R$ 8.940,00', 'detail' => 'Total de 14 títulos', 'tone' => 'red'],
    ];

    $transactions = [
        ['date' => '24/05/2026', 'description' => 'Repasses municipais para oficinas', 'category' => 'Repasses', 'status' => 'Liquidado', 'value' => '+ R$ 12.000,00'],
        ['date' => '22/05/2026', 'description' => 'Materiais pedagógicos - oficina de arte', 'category' => 'Oficinas', 'status' => 'Pendente', 'value' => '- R$ 4.250,00'],
        ['date' => '20/05/2026', 'description' => 'Manutenção da sede administrativa', 'category' => 'Manutenção', 'status' => 'Liquidado', 'value' => '- R$ 8.500,00'],
        ['date' => '18/05/2026', 'description' => 'Reembolso de despesas de transporte', 'category' => 'Transporte', 'status' => 'Liquidado', 'value' => '- R$ 1.120,45'],
        ['date' => '15/05/2026', 'description' => 'Doação destinada à alimentação', 'category' => 'Doações', 'status' => 'Liquidado', 'value' => '+ R$ 35.800,00'],
        ['date' => '12/05/2026', 'description' => 'Campanha de arrecadação comunitária', 'category' => 'Captação', 'status' => 'Pendente', 'value' => '+ R$ 5.000,00'],
    ];

    return view('transactions.index', compact('summary', 'transactions'));
})->name('transactions.index');

Route::get('/relatorios', function () {
    $distribution = [
        ['label' => 'Folha e pessoal', 'value' => 45],
        ['label' => 'Impostos e taxas', 'value' => 22],
        ['label' => 'Operacional e logística', 'value' => 18],
        ['label' => 'Outras despesas', 'value' => 15],
    ];

    $exports = [
        ['document' => 'fechamento_mensal_out26', 'description' => 'Relatório de fluxo de caixa', 'date' => '01/11/2026 - 09:42', 'format' => 'PDF', 'size' => '2.4 MB', 'status' => 'Processado'],
        ['document' => 'extrato_consolidado_v3', 'description' => 'Extrato consolidado', 'date' => '30/10/2026 - 18:15', 'format' => 'CSV', 'size' => '842 KB', 'status' => 'Processado'],
        ['document' => 'auditoria_anual_provisorio', 'description' => 'Relatório de provisões', 'date' => '25/10/2026 - 11:20', 'format' => 'PDF', 'size' => '15.7 MB', 'status' => 'Processado'],
        ['document' => 'log_importacao_recibos', 'description' => 'Arquivo pendente de revisão', 'date' => '24/10/2026 - 09:00', 'format' => 'XLS', 'size' => '0 KB', 'status' => 'Falha'],
    ];

    return view('reports.index', compact('distribution', 'exports'));
})->name('reports.index');
