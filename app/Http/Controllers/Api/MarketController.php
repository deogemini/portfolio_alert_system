<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketPrice;
use App\Models\Stock;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class MarketController extends Controller
{
    public function status()
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->get('https://dse.co.tz/market/data/overview');

            $serverDate = $response->header('Date');
            $eatNow = $serverDate
                ? Carbon::parse($serverDate)->setTimezone('Africa/Dar_es_Salaam')
                : Carbon::now('Africa/Dar_es_Salaam');
        } catch (\Throwable $e) {
            $eatNow = Carbon::now('Africa/Dar_es_Salaam');
        }

        $weekday = $eatNow->format('D');
        $isWeekday = in_array($weekday, ['Mon','Tue','Wed','Thu','Fri'], true);
        $openTime = $eatNow->copy()->setTime(10, 0, 0);
        $closeTime = $eatNow->copy()->setTime(14, 0, 0);
        $open = $isWeekday && $eatNow->between($openTime, $closeTime);

        return [
            'time_eat' => $eatNow->format('H:i:s'),
            'date_eat' => $eatNow->toDateString(),
            'open' => $open,
            'source' => 'dse_overview',
        ];
    }

    public function snapshot()
    {
        $symbols = [];
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            ])->get('https://dse.co.tz/market/data/overview');
            $html = $response->body();
            $symbols = $this->parseEquities($html);
        } catch (\Throwable $e) {
            $symbols = [];
        }

        $now = Carbon::now('Africa/Dar_es_Salaam');
        $saved = [];
        foreach ($symbols as $row) {
            $symbol = strtoupper($row['symbol']);
            $price = (float) $row['price'];
            $stock = Stock::firstOrCreate(['symbol' => $symbol]);
            $stock->update(['last_price' => $price, 'last_price_at' => $now]);
            MarketPrice::create([
                'stock_id' => $stock->id,
                'price' => $price,
                'fetched_at' => $now,
            ]);
            $saved[] = ['symbol' => $symbol, 'price' => $price];
        }

        return ['saved' => $saved, 'count' => count($saved)];
    }

    public function equities()
    {
        $stocks = Stock::with(['lots'])->orderBy('symbol')->get();
        $data = [];
        foreach ($stocks as $stock) {
            $latest = MarketPrice::where('stock_id', $stock->id)->orderByDesc('fetched_at')->first();
            $prev = MarketPrice::where('stock_id', $stock->id)->orderByDesc('fetched_at')->skip(1)->first();
            $price = $latest ? (float) $latest->price : ($stock->last_price ? (float) $stock->last_price : null);
            $prevPrice = $prev ? (float) $prev->price : null;
            $change = ($price !== null && $prevPrice !== null) ? $price - $prevPrice : null;
            $changePct = ($change !== null && $prevPrice) ? ($change / $prevPrice) * 100 : null;
            $data[] = [
                'symbol' => $stock->symbol,
                'price' => $price,
                'change' => $change,
                'change_pct' => $changePct,
            ];
        }
        return ['equities' => $data];
    }

    private function parseEquities(string $html): array
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();
        $xpath = new \DOMXPath($doc);
        $rows = $xpath->query('//table//tr');
        $out = [];
        foreach ($rows as $tr) {
            $cells = [];
            foreach ($xpath->query('.//td', $tr) as $td) {
                $cells[] = trim(preg_replace('/\s+/', ' ', $td->textContent));
            }
            if (count($cells) < 2) {
                continue;
            }
            $symbol = strtoupper($cells[0]);
            if (!preg_match('/^[A-Z0-9\.\-]{2,10}$/', $symbol)) {
                continue;
            }
            $priceCell = null;
            foreach ($cells as $c) {
                if (preg_match('/^\d{1,3}(?:,\d{3})*(?:\.\d+)?$|^\d+(?:\.\d+)?$/', $c)) {
                    $priceCell = $c;
                    break;
                }
            }
            if ($priceCell === null) {
                continue;
            }
            $price = (float) str_replace(',', '', $priceCell);
            if ($price <= 0) {
                continue;
            }
            $out[] = ['symbol' => $symbol, 'price' => $price];
        }
        return $out;
    }
}
