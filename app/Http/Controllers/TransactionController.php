<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\CashRegister;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(\App\Http\Requests\StoreTransactionRequest $request)
    {
        $activeRegister = CashRegister::where('user_id', Auth::id())
                                      ->where('status', 'aberto')
                                      ->first();

        if (!$activeRegister) {
            return back()->withErrors(['error' => 'Nenhum caixa aberto encontrado. É necessário abrir o caixa antes de registrar movimentações.']);
        }

        $receiptPath = null;
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        Transaction::create([
            'cash_register_id'  => $activeRegister->id,
            'type'              => $request->validated('type'),
            'amount'            => $request->validated('amount'),
            'description'       => $request->validated('description'),
            'payment_method'    => $request->validated('payment_method'),
            'receipt_path'      => $receiptPath,
            'competencia_date'  => $request->validated('competencia_date'),
            'notes'             => $request->validated('notes'),
        ]);

        return back()->with('success', 'Movimentação registrada com sucesso!');
    }

    // ─── Index (Extrato com filtros) ──────────────────────────────────────────

    public function index(Request $request)
    {
        $userCashRegisterIds = CashRegister::where('user_id', Auth::id())->pluck('id');

        $query = Transaction::whereIn('cash_register_id', $userCashRegisterIds)
                            ->orderBy('created_at', 'desc');

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }
        if ($request->filled('type') && in_array($request->type, ['entrada', 'saida'])) {
            $query->where('type', $request->type);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $allFiltered   = $query->get();
        $totalEntradas = $allFiltered->where('type', 'entrada')->sum('amount');
        $totalSaidas   = $allFiltered->where('type', 'saida')->sum('amount');
        $saldoConsolidado = $totalEntradas - $totalSaidas;

        $summary = [
            ['label' => 'Saldo do Histórico', 'value' => 'R$ ' . number_format($saldoConsolidado, 2, ',', '.'), 'detail' => 'Considera ' . $allFiltered->count() . ' lançamentos', 'tone' => 'blue'],
            ['label' => 'Total de Entradas',  'value' => 'R$ ' . number_format($totalEntradas, 2, ',', '.'),   'detail' => 'Entradas contabilizadas', 'tone' => 'green'],
            ['label' => 'Total de Saídas',    'value' => 'R$ ' . number_format($totalSaidas, 2, ',', '.'),     'detail' => 'Saídas e despesas', 'tone' => 'red'],
        ];

        $paginated  = $query->paginate(15)->withQueryString();
        $categories = ['Doações', 'Repasses', 'Oficinas', 'Manutenção', 'Pessoal', 'Alimentação', 'Transporte', 'Materiais'];

        return view('transactions.index', compact('summary', 'paginated', 'categories'));
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Transaction $transaction)
    {
        $owns = CashRegister::where('user_id', Auth::id())
                            ->where('id', $transaction->cash_register_id)
                            ->exists();
        if (!$owns) {
            abort(403);
        }

        if ($transaction->receipt_path) {
            Storage::disk('public')->delete($transaction->receipt_path);
        }

        $transaction->delete();

        return back()->with('success', 'Lançamento excluído com sucesso.');
    }

    // ─── Export CSV ───────────────────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $transactions = $this->filteredQuery($request)->get();
        $filename     = 'extrato_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Data Registro', 'Competência', 'Descrição', 'Tipo', 'Categoria', 'Valor (R$)', 'Observações'], ';');

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->created_at->format('d/m/Y H:i'),
                    $t->competencia_date ? \Carbon\Carbon::parse($t->competencia_date)->format('d/m/Y') : '-',
                    $t->description,
                    ucfirst($t->type),
                    $t->payment_method ?? '-',
                    number_format($t->amount, 2, ',', '.'),
                    $t->notes ?? '-',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Export PDF ───────────────────────────────────────────────────────────

    public function exportPdf(Request $request)
    {
        $transactions  = $this->filteredQuery($request)->get();
        $totalEntradas = $transactions->where('type', 'entrada')->sum('amount');
        $totalSaidas   = $transactions->where('type', 'saida')->sum('amount');
        $saldo         = $totalEntradas - $totalSaidas;

        $pdf      = Pdf::loadView('reports.pdf', compact('transactions', 'totalEntradas', 'totalSaidas', 'saldo'))
                       ->setPaper('a4', 'portrait');
        $filename = 'relatorio_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    // ─── Helper privado ───────────────────────────────────────────────────────

    private function filteredQuery(Request $request)
    {
        $ids   = CashRegister::where('user_id', Auth::id())->pluck('id');
        $query = Transaction::whereIn('cash_register_id', $ids)->orderBy('created_at', 'desc');

        if ($request->filled('date_start'))    $query->whereDate('created_at', '>=', $request->date_start);
        if ($request->filled('date_end'))      $query->whereDate('created_at', '<=', $request->date_end);
        if ($request->filled('type') && in_array($request->type, ['entrada', 'saida']))
                                               $query->where('type', $request->type);
        if ($request->filled('payment_method')) $query->where('payment_method', $request->payment_method);
        if ($request->filled('search'))        $query->where('description', 'like', '%' . $request->search . '%');

        return $query;
    }
}
