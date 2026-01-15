<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('buy_price', 12, 2);
            $table->decimal('take_profit_pct', 5, 2)->default(40);
            $table->decimal('buy_more_pct', 5, 2)->nullable();
            $table->timestamp('sell_notified_at')->nullable();
            $table->timestamp('buy_more_notified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lots');
    }
};
