<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\LotController;
use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MarketController;
use App\Http\Controllers\Api\ContactController;

Route::get('/', function () {
    return view('app');
});

Route::prefix('api')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('me', [AuthController::class, 'me'])->middleware('auth');

    Route::get('stocks', [StockController::class, 'index']);
    Route::post('stocks', [StockController::class, 'store'])->middleware('auth');
    Route::post('stocks/{symbol}/price', [StockController::class, 'updatePrice'])->middleware('auth');

    Route::get('lots', [LotController::class, 'index'])->middleware('auth');
    Route::post('lots', [LotController::class, 'store'])->middleware('auth');

    Route::post('alerts/check', [AlertController::class, 'check'])->middleware('auth');

    Route::get('market/status', [MarketController::class, 'status']);
    Route::post('market/snapshot', [MarketController::class, 'snapshot']);
    Route::get('market/equities', [MarketController::class, 'equities']);

    Route::post('contact', [ContactController::class, 'send']);
});
