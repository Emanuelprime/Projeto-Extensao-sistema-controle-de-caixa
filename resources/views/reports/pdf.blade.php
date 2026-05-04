<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Financeiro — Instituto JP II</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
        }

        .header {
            background: #0f172a;
            color: #fff;
            padding: 24px 28px;
            margin-bottom: 24px;
        }
        .header h1 { font-size: 18px; font-weight: 800; letter-spacing: 0.02em; }
        .header p  { font-size: 10px; margin-top: 4px; opacity: 0.7; }

        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 8px;
        }
        .summary-card {
            display: table-cell;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            width: 33%;
        }
        .summary-card .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; }
        .summary-card .value { font-size: 16px; font-weight: 800; margin-top: 4px; }
        .value-green { color: #15803d; }
        .value-red   { color: #b91c1c; }
        .value-blue  { color: #1d4ed8; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        thead { background: #0f172a; color: #fff; }
        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .badge-entrada {
            background: #dcfce7;
            color: #15803d;
            padding: 2px 6px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
        }
        .badge-saida {
            background: #fee2e2;
            color: #b91c1c;
            padding: 2px 6px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
        }
        .text-right { text-align: right; }
        .font-bold  { font-weight: 700; }

        .footer {
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
            margin-top: 16px;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Relatório Financeiro — Instituto JP II</h1>
        <p>Gerado em {{ now()->format('d/m/Y \à\s H:i') }}</p>
    </div>

    <div style="padding: 0 28px;">

        {{-- Resumo --}}
        <table class="summary-grid">
            <tr>
                <td class="summary-card">
                    <div class="label">Total de Entradas</div>
                    <div class="value value-green">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</div>
                </td>
                <td class="summary-card">
                    <div class="label">Total de Saídas</div>
                    <div class="value value-red">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</div>
                </td>
                <td class="summary-card">
                    <div class="label">Saldo Consolidado</div>
                    <div class="value value-blue">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
                </td>
            </tr>
        </table>

        {{-- Tabela de lançamentos --}}
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th class="text-right">Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $t)
                    <tr>
                        <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="font-bold">{{ $t->description }}</td>
                        <td>{{ $t->payment_method ?? '—' }}</td>
                        <td>
                            @if($t->type === 'entrada')
                                <span class="badge-entrada">↑ Receita</span>
                            @else
                                <span class="badge-saida">↓ Despesa</span>
                            @endif
                        </td>
                        <td class="text-right font-bold">
                            {{ number_format($t->amount, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px; color: #94a3b8;">
                            Nenhum lançamento encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Instituto JP II · Sistema de Controle de Caixa · {{ $transactions->count() }} lançamento(s)
        </div>

    </div>
</body>
</html>
