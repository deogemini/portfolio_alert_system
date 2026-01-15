<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Notifications\PriceAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Carbon;

class AlertController extends Controller
{
    public function check(Request $request)
    {
        $user = auth()->user();
        $results = [];

        $lots = Lot::with('stock')->where('user_id', auth()->id())->get();
        foreach ($lots as $lot) {
            $price = $lot->stock->last_price;
            if ($price === null) {
                continue;
            }
            $sellTarget = $lot->buy_price * (1 + ($lot->take_profit_pct / 100));
            $buyTarget = $lot->buy_more_pct ? $lot->buy_price * (1 - ($lot->buy_more_pct / 100)) : null;

            if ($lot->sell_notified_at === null && $price >= $sellTarget && $user) {
                Notification::send($user, new PriceAlert(
                    'SELL',
                    $lot->stock->symbol,
                    $lot->quantity,
                    (float) $lot->buy_price,
                    (float) $price,
                    (float) $sellTarget
                ));
                $lot->sell_notified_at = Carbon::now();
                $lot->save();
                $results[] = ['type' => 'SELL', 'symbol' => $lot->stock->symbol, 'lot_id' => $lot->id];
            }

            if ($buyTarget !== null && $lot->buy_more_notified_at === null && $price <= $buyTarget && $user) {
                Notification::send($user, new PriceAlert(
                    'BUY_MORE',
                    $lot->stock->symbol,
                    $lot->quantity,
                    (float) $lot->buy_price,
                    (float) $price,
                    (float) $buyTarget
                ));
                $lot->buy_more_notified_at = Carbon::now();
                $lot->save();
                $results[] = ['type' => 'BUY_MORE', 'symbol' => $lot->stock->symbol, 'lot_id' => $lot->id];
            }
        }

        return ['notified' => $results];
    }
}
