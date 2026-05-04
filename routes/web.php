<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;

Route::redirect('/', '/login');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ─── Rotas protegidas (usuário autenticado) ───────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [CashRegisterController::class, 'dashboard'])->name('dashboard');

    // Ações de Caixa
    Route::post('/caixa/abrir',  [CashRegisterController::class, 'open'])->name('cash_register.open');
    Route::post('/caixa/fechar', [CashRegisterController::class, 'close'])->name('cash_register.close');

    // Transações
    Route::get('/lancamentos/novo', function () {
        $categories = ['Doações', 'Repasses', 'Oficinas', 'Manutenção', 'Pessoal', 'Alimentação', 'Transporte', 'Materiais'];
        return view('transactions.create', compact('categories'));
    })->name('transactions.create');

    Route::post('/lancamentos',              [TransactionController::class, 'store'])->name('transactions.store');
    Route::delete('/lancamentos/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Extrato com filtros
    Route::get('/extrato', [TransactionController::class, 'index'])->name('transactions.index');

    Route::get('/administradores/novo', function () {
        return view('admin-users.create');
    })->name('admins.create');

    // Exportações
    Route::get('/extrato/exportar-csv', [TransactionController::class, 'exportCsv'])->name('transactions.export_csv');
    Route::get('/extrato/exportar-pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export_pdf');

    // Relatórios
    Route::get('/relatorios', [ReportController::class, 'index'])->name('reports.index');

    // ─── Gestão de Usuários (somente admin — verificado no controller) ────────
    Route::get('/usuarios',              [UserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/novo',         [UserController::class, 'create'])->name('users.create');
    Route::post('/usuarios',             [UserController::class, 'store'])->name('users.store');
    Route::get('/usuarios/{user}/editar',[UserController::class, 'edit'])->name('users.edit');
    Route::put('/usuarios/{user}',       [UserController::class, 'update'])->name('users.update');
});
