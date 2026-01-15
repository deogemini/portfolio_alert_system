<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\Stock;
use Illuminate\Http\Request;

class LotController extends Controller
{
    public function index()
    {
        return Lot::with('stock')->where('user_id', auth()->id())->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'integer', 'min:1'],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'take_profit_pct' => ['nullable', 'numeric', 'min:0'],
            'buy_more_pct' => ['nullable', 'numeric', 'min:0'],
        ]);

        $stock = Stock::firstOrCreate(
            ['symbol' => strtoupper($data['symbol'])]
        );

        $lot = Lot::create([
            'user_id' => auth()->id(),
            'stock_id' => $stock->id,
            'quantity' => $data['quantity'],
            'buy_price' => $data['buy_price'],
            'take_profit_pct' => $data['take_profit_pct'] ?? 40,
            'buy_more_pct' => $data['buy_more_pct'] ?? null,
        ]);

        return $lot->load('stock');
    }
}
