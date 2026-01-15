<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Lot extends Model
{
    protected $fillable = [
        'user_id',
        'stock_id',
        'quantity',
        'buy_price',
        'take_profit_pct',
        'buy_more_pct',
        'sell_notified_at',
        'buy_more_notified_at',
    ];

    protected $casts = [
        'buy_price' => 'decimal:2',
        'take_profit_pct' => 'decimal:2',
        'buy_more_pct' => 'decimal:2',
        'sell_notified_at' => 'datetime',
        'buy_more_notified_at' => 'datetime',
    ];

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
