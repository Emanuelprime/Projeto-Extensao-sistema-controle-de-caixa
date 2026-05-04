<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_register_id',
        'type',
        'amount',
        'description',
        'payment_method',
        'receipt_path',
        'competencia_date',
        'notes',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'competencia_date' => 'date',
    ];

    /**
     * Get the cash register that owns the transaction.
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }
}
