<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StockController extends Controller
{
    public function index()
    {
        return Stock::orderBy('symbol')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $stock = Stock::firstOrCreate(
            ['symbol' => strtoupper($data['symbol'])],
            ['name' => $data['name'] ?? null]
        );

        return $stock->fresh();
    }

    public function updatePrice(Request $request, string $symbol)
    {
        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $stock = Stock::where('symbol', strtoupper($symbol))->firstOrFail();
        $stock->update([
            'last_price' => $data['price'],
            'last_price_at' => Carbon::now(),
        ]);

        return $stock->fresh();
    }
}

