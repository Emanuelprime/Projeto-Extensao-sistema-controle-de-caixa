<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\TransactionController;

Route::redirect('/', '/login');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rotas protegidas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [CashRegisterController::class, 'dashboard'])->name('dashboard');
    
    // Ações de Caixa
    Route::post('/caixa/abrir', [CashRegisterController::class, 'open'])->name('cash_register.open');
    Route::post('/caixa/fechar', [CashRegisterController::class, 'close'])->name('cash_register.close');

    // Transações
    Route::get('/lancamentos/novo', function () {
        $categories = ['Doações', 'Repasses', 'Oficinas', 'Manutenção', 'Pessoal', 'Alimentação', 'Transporte', 'Materiais'];
        return view('transactions.create', compact('categories'));
    })->name('transactions.create');
    
    Route::post('/lancamentos', [TransactionController::class, 'store'])->name('transactions.store');

    // Rotas de Relatórios ou Extras (ainda parciais)
    Route::get('/extrato', [TransactionController::class, 'index'])->name('transactions.index');

    Route::get('/relatorios', function () {
        $distribution = [
            ['label' => 'Folha e pessoal', 'value' => 45],
            ['label' => 'Impostos e taxas', 'value' => 22],
        ];

        $exports = [
            ['document' => 'fechamento_mensal_out26', 'description' => 'Relatório de fluxo de caixa', 'date' => '01/11/2026 - 09:42', 'format' => 'PDF', 'size' => '2.4 MB', 'status' => 'Processado'],
        ];

        return view('reports.index', compact('distribution', 'exports'));
    })->name('reports.index');
});
