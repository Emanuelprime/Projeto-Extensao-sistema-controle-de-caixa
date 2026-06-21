<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportHistory extends Model
{
    protected $fillable = [
        'user_id',
        'document',
        'description',
        'format',
        'filename',
        'path',
        'size_bytes',
        'status',
        'filters',
    ];

    protected $casts = [
        'filters' => 'array',
        'size_bytes' => 'integer',
    ];
}
