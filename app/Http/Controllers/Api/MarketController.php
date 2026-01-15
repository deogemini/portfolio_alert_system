<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketPrice;
use App\Models\Stock;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            ])->timeout(10)->get('https://vertex.co.tz/');
            $html = $response->body();
            $symbols = $this->parseVertexEquities($html);
        } catch (\Throwable $e) {
            Log::warning('Market snapshot vertex fetch failed', [
                'error' => $e->getMessage(),
            ]);
            $symbols = [];
        }

        // Fallback to DSE overview if Vertex did not return anything
        if (!count($symbols)) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ])->timeout(10)->get('https://dse.co.tz/market/data/overview');
                $html = $response->body();
                $symbols = $this->parseDseEquities($html);
            } catch (\Throwable $e) {
                Log::error('Market snapshot DSE fetch failed', [
                    'error' => $e->getMessage(),
                ]);
                $symbols = [];
            }
        }

        $now = Carbon::now('Africa/Dar_es_Salaam');
        $saved = [];
        foreach ($symbols as $row) {
            $symbol = strtoupper($row['symbol']);
            $price = (float) $row['price'];
            $changePct = isset($row['change_pct']) ? (float) $row['change_pct'] : 0.0;

            // Calculate change value from percentage
            // price = prev_price * (1 + pct/100)
            // prev_price = price / (1 + pct/100)
            // change = price - prev_price
            $change = 0.0;
            if ($price > 0 && $changePct !== 0.0) {
                 $prevPrice = $price / (1 + ($changePct / 100));
                 $change = $price - $prevPrice;
            }

            $stock = Stock::firstOrCreate(['symbol' => $symbol]);
            $stock->update([
                'last_price' => $price,
                'change' => $change,
                'change_pct' => $changePct,
                'last_price_at' => $now
            ]);

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
        $stocks = Stock::orderBy('symbol')->get();
        $data = [];
        foreach ($stocks as $stock) {
            $data[] = [
                'symbol' => $stock->symbol,
                'price' => (float) $stock->last_price,
                'change' => (float) $stock->change,
                'change_pct' => (float) $stock->change_pct,
            ];
        }
        return ['equities' => $data];
    }

    private function parseVertexEquities(string $html): array
    {
        // Extract the ticker items JSON
        if (preg_match('/"items":(\[\{.*?\}\])/', $html, $matches)) {
            $items = json_decode($matches[1], true);
            if (isset($items[0]['html'])) {
                $tickerHtml = $items[0]['html'];

                // Parse the HTML content for stocks
                // Format: SYMBOL: PRICE <span...>... CHANGE%</span>
                // Example: AFRIPRISE: 475 <span class="down">\u25bc -2.06%</span>

                $out = [];
                // Regex to capture Symbol, Price, and Change%
                // Handles unescaped or escaped unicode characters in span
                if (preg_match_all('/([A-Z0-9]+):\s+([0-9,]+)\s+<span[^>]*>.*?([+\-]?\d+\.\d+)%<\/span>/', $tickerHtml, $m, PREG_SET_ORDER)) {
                    foreach ($m as $match) {
                        $symbol = $match[1];
                        $price = (float) str_replace(',', '', $match[2]);
                        $changePct = (float) $match[3];

                        $out[] = [
                            'symbol' => $symbol,
                            'price' => $price,
                            'change_pct' => $changePct
                        ];
                    }
                }

                // Also handle neutral (0.00%) which might have different format or be consistent
                // The regex above expects a number followed by % inside span.
                // Example: JSL: 5 <span class="neutral">... 0.00%</span>
                // It should match.

                return $out;
            }
        }
        return [];
    }

    private function parseDseEquities(string $html): array
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
            $out[] = [
                'symbol' => $symbol,
                'price' => $price,
                'change_pct' => 0.0,
            ];
        }
        return $out;
    }
}
