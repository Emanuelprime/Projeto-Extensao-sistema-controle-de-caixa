<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Transaction;
use App\Models\CashRegister;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request)
    {
        // Identificar o caixa aberto do usuário atual
        $activeRegister = CashRegister::where('user_id', Auth::id())
                                      ->where('status', 'aberto')
                                      ->first();

        if (!$activeRegister) {
            return back()->withErrors(['error' => 'Nenhum caixa aberto encontrado. É necessário abrir o caixa antes de registrar movimentações.']);
        }

        Transaction::create([
            'cash_register_id' => $activeRegister->id,
            'type' => $request->validated('type'),
            'amount' => $request->validated('amount'),
            'description' => $request->validated('description'),
            'payment_method' => $request->validated('payment_method'),
        ]);

        return back()->with('success', 'Movimentação registrada com sucesso!');
    }

    public function index()
    {
        // Buscar relatórios de todos os caixas desse usuário ativo
        $userCashRegisters = CashRegister::where('user_id', Auth::id())->pluck('id');
        $allTransactions = Transaction::whereIn('cash_register_id', $userCashRegisters)
                                      ->orderBy('created_at', 'desc')
                                      ->get();

        $totalEntradas = $allTransactions->where('type', 'entrada')->sum('amount');
        $totalSaidas = $allTransactions->where('type', 'saida')->sum('amount');
        $saldoConsolidado = $totalEntradas - $totalSaidas;

        $summary = [
            ['label' => 'Saldo do Histórico', 'value' => 'R$ ' . number_format($saldoConsolidado, 2, ',', '.'), 'detail' => 'Considera ' . $allTransactions->count() . ' lançamentos', 'tone' => 'blue'],
            ['label' => 'Total de Entradas', 'value' => 'R$ ' . number_format($totalEntradas, 2, ',', '.'), 'detail' => 'Entradas contabilizadas', 'tone' => 'green'],
            ['label' => 'Total de Saídas', 'value' => 'R$ ' . number_format($totalSaidas, 2, ',', '.'), 'detail' => 'Saídas e despesas', 'tone' => 'red'],
        ];

        $transactions = [];
        foreach ($allTransactions as $t) {
            $transactions[] = [
                'date' => $t->created_at->format('d/m/Y'),
                'description' => $t->description,
                'category' => $t->payment_method ?? 'Transação Real',
                'status' => 'Liquidado',
                'value' => ($t->type == 'entrada' ? '+' : '-') . ' R$ ' . number_format($t->amount, 2, ',', '.')
            ];
        }

        return view('transactions.index', compact('summary', 'transactions'));
    }
}
