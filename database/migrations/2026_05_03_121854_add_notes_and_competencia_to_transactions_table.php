<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('competencia_date')->nullable()->after('receipt_path')
                  ->comment('Data de competência da transação (pode diferir da data de registro)');
            $table->text('notes')->nullable()->after('competencia_date')
                  ->comment('Observações complementares sobre o lançamento');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['competencia_date', 'notes']);
        });
    }
};
