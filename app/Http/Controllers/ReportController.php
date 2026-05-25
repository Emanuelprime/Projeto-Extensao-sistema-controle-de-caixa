<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Popular os selects com dados reais do banco
        $realBankNames = Transaction::whereNotNull('bank_name')
            ->where('bank_name', '!=', '')
            ->distinct()
            ->pluck('bank_name')
            ->toArray();

        $realBankAccounts = Transaction::whereNotNull('bank_account')
            ->where('bank_account', '!=', '')
            ->distinct()
            ->pluck('bank_account')
            ->toArray();

        // Categorias reais = categorias padrão + customizadas criadas
        $defaultCategories = ['Doações', 'Repasses', 'Oficinas', 'Manutenção', 'Pessoal', 'Alimentação', 'Transporte', 'Materiais', 'Despesas Administrativas'];
        $customCategories = \App\Models\Category::pluck('name')->toArray();
        $realCategories = array_unique(array_merge($defaultCategories, $customCategories));
        sort($realCategories);

        // 2. Aplicar filtros múltiplos nas transações de forma combinada
        $query = Transaction::orderBy('created_at', 'desc');

        if ($request->filled('bank_names')) {
            $query->whereIn('bank_name', $request->input('bank_names'));
        }
        if ($request->filled('bank_accounts')) {
            $query->whereIn('bank_account', $request->input('bank_accounts'));
        }
        if ($request->filled('payment_methods')) {
            $query->whereIn('payment_method', $request->input('payment_methods'));
        }

        $filteredTransactions = $query->get();

        // Totais gerais com base nos dados filtrados
        $totalEntradas = $filteredTransactions->where('type', 'entrada')->sum('amount');
        $totalSaidas   = $filteredTransactions->where('type', 'saida')->sum('amount');
        $saldo         = $totalEntradas - $totalSaidas;

        // Distribuição por categoria (payment_method) com base nos dados filtrados
        $distribution = $filteredTransactions
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

        // Histórico de exportações
        $exports = [];

        // Definir as variáveis exatas exigidas pela view do frontend
        $selectedBanks = (array) $request->input('bank_names', []);
        $selectedAccounts = (array) $request->input('bank_accounts', []);
        $selectedCategories = (array) $request->input('payment_methods', []);

        $filterBanks = $realBankNames;
        $filterAccounts = $realBankAccounts;
        $filterCategories = $realCategories;

        $reportFilters = $request->only(['bank_names', 'bank_accounts', 'payment_methods']);

        return view('reports.index', compact(
            'distributionFormatted',
            'totalEntradas',
            'totalSaidas',
            'saldo',
            'exports',
            'selectedBanks',
            'selectedAccounts',
            'selectedCategories',
            'filterBanks',
            'filterAccounts',
            'filterCategories',
            'reportFilters'
        ));
    }
}
