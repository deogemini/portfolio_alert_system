<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'last_price',
        'change',
        'change_pct',
        'last_price_at',
    ];

    public function lots(): HasMany
    {
        return $this->hasMany(Lot::class);
    }
}

