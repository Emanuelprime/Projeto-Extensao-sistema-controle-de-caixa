<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpenCashRegisterRequest;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends Controller
{
    // Método para exibir o Dashboard do Caixa
    public function dashboard()
    {
        $activeRegister = CashRegister::where('user_id', Auth::id())
                                      ->where('status', 'aberto')
                                      ->with('transactions')
                                      ->first();
                                      
        $entradas = 0;
        $saidas = 0;
        $saldoAtual = 0;

        if ($activeRegister) {
            $entradas = $activeRegister->transactions->where('type', 'entrada')->sum('amount');
            $saidas = $activeRegister->transactions->where('type', 'saida')->sum('amount');
            $saldoAtual = $activeRegister->opening_balance + $entradas - $saidas;
        }

        // Recuperar mockups temporários para não quebrar a tela atual
        $indicators = [
            ['label' => 'Saldo atual', 'value' => 'R$ '.number_format($saldoAtual, 2, ',', '.'), 'detail' => 'Caixa consolidado para uso interno', 'tone' => 'blue'],
            ['label' => 'Entradas deste turno', 'value' => 'R$ '.number_format($entradas, 2, ',', '.'), 'detail' => 'Somatório de todas entradas', 'tone' => 'green'],
            ['label' => 'Saídas deste turno', 'value' => 'R$ '.number_format($saidas, 2, ',', '.'), 'detail' => 'Somatório de todas saídas', 'tone' => 'red'],
        ];

        // Se houver transações reais, exibe, caso contrário mock
        $transactions = [];
        if ($activeRegister && $activeRegister->transactions->count() > 0) {
            foreach ($activeRegister->transactions as $t) {
                $transactions[] = [
                    'date' => $t->created_at->format('d/m/Y H:i'),
                    'description' => $t->description,
                    'category' => 'Transação',
                    'type' => ucfirst($t->type),
                    'value' => ($t->type == 'entrada' ? '+' : '-') . ' R$ ' . number_format($t->amount, 2, ',', '.'),
                    'status' => 'Liquidado'
                ];
            }
        } else {
            $transactions = [
                ['date' => '14 Out 2026', 'description' => 'Exemplo de movimentação', 'category' => 'Teste', 'type' => 'Entrada', 'value' => '+ R$ 0,00', 'status' => 'Pendente']
            ];
        }

        $distribution = [
            ['label' => 'Oficinas', 'value' => 45],
            ['label' => 'Pessoal', 'value' => 28],
            ['label' => 'Manutenção', 'value' => 15],
            ['label' => 'Outros', 'value' => 12],
        ];

        // Variáveis disponibilizadas para o frontend blade
        return view('dashboard', compact('activeRegister', 'entradas', 'saidas', 'saldoAtual', 'indicators', 'transactions', 'distribution'));
    }

    public function open(OpenCashRegisterRequest $request)
    {
        if (CashRegister::where('user_id', Auth::id())->where('status', 'aberto')->exists()) {
            return back()->withErrors(['error' => 'Você já possui um caixa aberto no momento. Feche-o antes de abrir outro.']);
        }

        CashRegister::create([
            'user_id' => Auth::id(),
            'status' => 'aberto',
            'opening_balance' => $request->validated('opening_balance'),
            'opened_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Caixa aberto com sucesso!');
    }

    public function close(Request $request)
    {
        $activeRegister = CashRegister::where('user_id', Auth::id())
                                      ->where('status', 'aberto')
                                      ->first();

        if (!$activeRegister) {
            return back()->withErrors(['error' => 'Nenhum caixa aberto no momento para fechar.']);
        }

        $entradas = $activeRegister->transactions()->where('type', 'entrada')->sum('amount');
        $saidas = $activeRegister->transactions()->where('type', 'saida')->sum('amount');
        $expectedBalance = $activeRegister->opening_balance + $entradas - $saidas;

        $activeRegister->update([
            'status' => 'fechado',
            // Pega o valor real contado na gaveta ou assume o valor esperado pelo sistema
            'closing_balance' => $request->input('closing_balance', $expectedBalance), 
            'closed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Caixa fechado com sucesso! Saldo Finalizado: R$ ' . number_format($expectedBalance, 2, ',', '.'));
    }
}
