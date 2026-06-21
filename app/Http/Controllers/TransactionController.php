<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\CashRegister;
use App\Models\ExportHistory;
use App\Support\FinanceOptions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(\App\Http\Requests\StoreTransactionRequest $request)
    {
        $activeRegister = CashRegister::where('status', 'aberto')
                                      ->first();

        if (!$activeRegister) {
            return back()->withErrors(['error' => 'Nenhum caixa aberto encontrado. É necessário abrir o caixa antes de registrar movimentações.']);
        }

        $receiptPath = null;
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $paymentMethod = $request->validated('payment_method');
        $defaultCategories = FinanceOptions::defaultCategories();
        $defaultCategories = ['Doações', 'Repasses', 'Oficinas', 'Manutenção', 'Pessoal', 'Alimentação', 'Transporte', 'Materiais', 'Despesas Administrativas'];
        if ($paymentMethod && !in_array($paymentMethod, FinanceOptions::defaultCategories(), true)) {
            \App\Models\Category::firstOrCreate(['name' => $paymentMethod]);
        }

        $bankName = $request->validated('bank_name');
        if ($bankName && !in_array($bankName, FinanceOptions::defaultBanks(), true)) {
            \App\Models\Bank::firstOrCreate(['name' => $bankName]);
        }

        Transaction::create([
            'cash_register_id'  => $activeRegister->id,
            'type'              => $request->validated('type'),
            'amount'            => $request->validated('amount'),
            'description'       => $request->validated('description'),
            'payment_method'    => $paymentMethod,
            'bank_name'         => $bankName,
            'bank_account'      => $request->validated('bank_account'),
            'receipt_path'      => $receiptPath,
            'competencia_date'  => $request->validated('competencia_date'),
            'notes'             => $request->validated('notes'),
        ]);

        return back()->with('success', 'Movimentação registrada com sucesso!');
    }

    // ─── Index (Extrato com filtros) ──────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Transaction::orderBy('created_at', 'desc');

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
        $defaultCategories = ['Doações', 'Repasses', 'Oficinas', 'Manutenção', 'Pessoal', 'Alimentação', 'Transporte', 'Materiais', 'Despesas Administrativas'];
        $customCategories = \App\Models\Category::pluck('name')->toArray();
        $categories = array_unique(array_merge($defaultCategories, $customCategories));
        sort($categories);
        $categories = FinanceOptions::categories();

        return view('transactions.index', compact('summary', 'paginated', 'categories'));
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Transaction $transaction)
    {
        // Permite excluir qualquer transação se for admin ou se o caixa ainda existir
        if (!Auth::user()->isAdmin() && !$transaction->cashRegister) {
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

        $content = $this->buildCsv($transactions);
        $this->recordExport($request, $filename, $content, 'CSV', 'Relatório financeiro em CSV');

        return response($content, 200, $headers);

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['Data Registro', 'Competência', 'Descrição', 'Tipo', 'Categoria', 'Banco', 'Conta Bancária', 'Valor (R$)', 'Observações'], ';');

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->created_at->format('d/m/Y H:i'),
                    $t->competencia_date ? \Carbon\Carbon::parse($t->competencia_date)->format('d/m/Y') : '-',
                    $t->description,
                    ucfirst($t->type),
                    $t->payment_method ?? '-',
                    $t->bank_name ?? '-',
                    $t->bank_account ?? '-',
                    number_format($t->amount, 2, ',', '.'),
                    $t->notes ?? '-',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── Export PDF ───────────────────────────────────────────────────────────

    public function exportXlsx(Request $request)
    {
        $transactions = $this->filteredQuery($request)->get();
        $filename = 'extrato_' . now()->format('Y-m-d_His') . '.xlsx';
        $content = $this->buildXlsx($transactions);

        $this->recordExport($request, $filename, $content, 'XLSX', 'Relatório financeiro em XLSX');

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportPdf(Request $request)
    {
        $transactions  = $this->filteredQuery($request)->get();
        $totalEntradas = $transactions->where('type', 'entrada')->sum('amount');
        $totalSaidas   = $transactions->where('type', 'saida')->sum('amount');
        $saldo         = $totalEntradas - $totalSaidas;

        $pdf      = Pdf::loadView('reports.pdf', compact('transactions', 'totalEntradas', 'totalSaidas', 'saldo'))
                       ->setPaper('a4', 'portrait');
        $filename = 'relatorio_' . now()->format('Y-m-d_His') . '.pdf';
        $content = $pdf->output();

        $this->recordExport($request, $filename, $content, 'PDF', 'Relatório financeiro em PDF');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadExport(ExportHistory $exportHistory)
    {
        abort_unless(Storage::disk('local')->exists($exportHistory->path), 404);

        return Storage::disk('local')->download($exportHistory->path, $exportHistory->filename);
    }

    // ─── Helper privado ───────────────────────────────────────────────────────

    private function filteredQuery(Request $request)
    {
        $query = Transaction::orderBy('created_at', 'desc');

        if ($request->filled('date_start'))    $query->whereDate('created_at', '>=', $request->date_start);
        if ($request->filled('date_end'))      $query->whereDate('created_at', '<=', $request->date_end);
        if ($request->filled('type') && in_array($request->type, ['entrada', 'saida']))
                                               $query->where('type', $request->type);
        if ($request->filled('search'))        $query->where('description', 'like', '%' . $request->search . '%');

        // Filtros múltiplos de bancos, contas e categorias
        if ($request->filled('bank_names')) {
            $query->whereIn('bank_name', $request->input('bank_names'));
        }
        if ($request->filled('bank_accounts')) {
            $query->whereIn('bank_account', $request->input('bank_accounts'));
        }
        if ($request->filled('payment_methods')) {
            $query->whereIn('payment_method', $request->input('payment_methods'));
        }

        // Filtro de categoria única (caso venha do extrato simples)
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return $query;
    }

    private function buildCsv($transactions): string
    {
        $handle = fopen('php://temp', 'r+');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, ['Data Registro', 'Competência', 'Descrição', 'Tipo', 'Categoria', 'Banco', 'Conta Bancária', 'Valor (R$)', 'Observações'], ';');

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->created_at->format('d/m/Y H:i'),
                $t->competencia_date ? \Carbon\Carbon::parse($t->competencia_date)->format('d/m/Y') : '-',
                $t->description,
                ucfirst($t->type),
                $t->payment_method ?? '-',
                $t->bank_name ?? '-',
                $t->bank_account ?? '-',
                number_format($t->amount, 2, ',', '.'),
                $t->notes ?? '-',
            ], ';');
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    private function buildXlsx($transactions): string
    {
        $rows = [
            ['Data Registro', 'Competência', 'Descrição', 'Tipo', 'Categoria', 'Banco', 'Conta Bancária', 'Valor (R$)', 'Observações'],
        ];

        foreach ($transactions as $t) {
            $rows[] = [
                $t->created_at->format('d/m/Y H:i'),
                $t->competencia_date ? \Carbon\Carbon::parse($t->competencia_date)->format('d/m/Y') : '-',
                $t->description,
                ucfirst($t->type),
                $t->payment_method ?? '-',
                $t->bank_name ?? '-',
                $t->bank_account ?? '-',
                number_format($t->amount, 2, ',', '.'),
                $t->notes ?? '-',
            ];
        }

        $sheetData = '';
        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 1;
            $sheetData .= '<row r="' . $excelRow . '">';

            foreach ($row as $columnIndex => $value) {
                $cellRef = $this->xlsxColumnName($columnIndex + 1) . $excelRow;
                $sheetData .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $this->xmlEscape((string) $value) . '</t></is></c>';
            }

            $sheetData .= '</row>';
        }

        $files = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="Extrato" sheetId="1" r:id="rId1"/></sheets>'
                . '</workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                . '</Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
                . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
                . '<borders count="1"><border/></borders>'
                . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
                . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellXfs>'
                . '</styleSheet>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<sheetData>' . $sheetData . '</sheetData>'
                . '</worksheet>',
        ];

        return $this->buildZip($files);
    }

    private function buildZip(array $files): string
    {
        $localData = '';
        $centralDirectory = '';

        foreach ($files as $name => $content) {
            $offset = strlen($localData);
            $crc = hexdec(hash('crc32b', $content));
            $size = strlen($content);
            $nameLength = strlen($name);

            $localData .= pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0)
                . $name
                . $content;

            $centralDirectory .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, 0, 0, $crc, $size, $size, $nameLength, 0, 0, 0, 0, 0, $offset)
                . $name;
        }

        return $localData
            . $centralDirectory
            . pack('VvvvvVVv', 0x06054b50, 0, 0, count($files), count($files), strlen($centralDirectory), strlen($localData), 0);
    }

    private function xlsxColumnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function recordExport(Request $request, string $filename, string $content, string $format, string $description): void
    {
        $path = 'reports/' . $filename;

        Storage::disk('local')->put($path, $content);

        ExportHistory::create([
            'user_id' => Auth::id(),
            'document' => $filename,
            'description' => $description,
            'format' => $format,
            'filename' => $filename,
            'path' => $path,
            'size_bytes' => strlen($content),
            'status' => 'Processado',
            'filters' => $request->query(),
        ]);
    }
}
