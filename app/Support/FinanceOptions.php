<?php

namespace App\Support;

use App\Models\Bank;
use App\Models\Category;
use App\Models\Transaction;

class FinanceOptions
{
    public static function defaultCategories(): array
    {
        return ['Doações', 'Repasses', 'Oficinas', 'Manutenção', 'Pessoal', 'Alimentação', 'Transporte', 'Materiais', 'Despesas Administrativas'];
    }

    public static function defaultBanks(): array
    {
        return ['Banco do Brasil', 'Caixa Econômica Federal', 'Bradesco', 'Itaú', 'Santander', 'Nubank', 'Inter', 'Sicoob', 'Sicredi'];
    }

    public static function categories(): array
    {
        return self::sortedUnique(array_merge(
            self::defaultCategories(),
            Category::pluck('name')->toArray()
        ));
    }

    public static function banks(bool $includeTransactions = false): array
    {
        $banks = array_merge(
            self::defaultBanks(),
            Bank::pluck('name')->toArray()
        );

        if ($includeTransactions) {
            $banks = array_merge($banks, Transaction::whereNotNull('bank_name')
                ->where('bank_name', '!=', '')
                ->distinct()
                ->pluck('bank_name')
                ->toArray());
        }

        return self::sortedUnique($banks);
    }

    private static function sortedUnique(array $values): array
    {
        $values = array_values(array_unique(array_filter($values)));
        sort($values);

        return $values;
    }
}
