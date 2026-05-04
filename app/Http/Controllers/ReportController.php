<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $userCashRegisterIds = CashRegister::where('user_id', Auth::id())->pluck('id');

        $allTransactions = Transaction::whereIn('cash_register_id', $userCashRegisterIds)->get();

        // Totais gerais
        $totalEntradas = $allTransactions->where('type', 'entrada')->sum('amount');
        $totalSaidas   = $allTransactions->where('type', 'saida')->sum('amount');
        $saldo         = $totalEntradas - $totalSaidas;

        // Distribuição por categoria (payment_method)
        $distribution = $allTransactions
            ->groupBy('payment_method')
            ->map(fn($group) => $group->sum('amount'))
            ->sortDesc();

        $totalGeral = $distribution->sum() ?: 1; // evitar divisão por zero

        $distributionFormatted = $distribution->map(function ($valor, $label) use ($totalGeral) {
            return [
                'label' => $label ?: 'Sem categoria',
                'value' => round(($valor / $totalGeral) * 100),
                'amount' => $valor,
            ];
        })->values()->toArray();

        // Histórico de exportações (futuro: pode ser uma tabela separada)
        // Por ora, listamos um registro simbólico se houver transações
        $exports = [];

        return view('reports.index', compact('distributionFormatted', 'totalEntradas', 'totalSaidas', 'saldo', 'exports'));
    }
}
