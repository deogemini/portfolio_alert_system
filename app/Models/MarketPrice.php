<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketPrice extends Model
{
    protected $fillable = [
        'stock_id',
        'price',
        'fetched_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'fetched_at' => 'datetime',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}

